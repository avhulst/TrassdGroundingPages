<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\EventListener\DataContainer;

use Contao\DataContainer;
use Contao\Message;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Trassd\Contao\GroundingPages\EventListener\DataContainer\DcaSiblingReader;
use Trassd\Contao\GroundingPages\EventListener\DataContainer\ReferenceFieldsListener;
use Trassd\Contao\GroundingPages\Reference\OwnHostsProvider;
use Trassd\Contao\GroundingPages\Reference\SameAsPolicy;
use Trassd\Contao\GroundingPages\Review\ReviewFieldsValidator;

class ReferenceFieldsListenerTest extends ContaoTestCase
{
    public function testV2BlocksOwnDomainSameAs(): void
    {
        $listener = $this->listener(['reviewStandard' => '2.0', 'entityId' => 'https://example.com/about/#organization']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tl_grounding_page.error.sameAs.ownDomain');

        $listener->onSaveSameAs(serialize(['https://example.com/press']), $this->dc());
    }

    public function testV2AcceptsValidSameAs(): void
    {
        $listener = $this->listener(['reviewStandard' => '2.0']);
        $value = serialize(['https://www.wikidata.org/wiki/Q42944']);

        $this->assertSame($value, $listener->onSaveSameAs($value, $this->dc()));
    }

    public function testV17OnlyAddsInfoMessage(): void
    {
        $message = $this->createAdapterMock(['addInfo']);
        $message
            ->expects($this->once())
            ->method('addInfo')
        ;

        $listener = $this->listener(['reviewStandard' => '1.7'], $message);
        $value = serialize(['https://doi.org/10.1000/182']);

        $this->assertSame($value, $listener->onSaveSameAs($value, $this->dc()));
    }

    public function testRootPageDnsCountsAsOwnDomain(): void
    {
        $listener = $this->listener(['reviewStandard' => '2.0'], null, ['example.net']);

        $this->expectException(\RuntimeException::class);

        $listener->onSaveSameAs(serialize(['https://www.example.net/']), $this->dc());
    }

    public function testRelativeEntityIdIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tl_grounding_page.error.notAbsoluteUrl');

        $this->listener([])->onSaveAbsoluteUrl('/about/#organization', $this->dc());
    }

    public function testEmptyEntityIdIsAllowed(): void
    {
        $this->assertSame('', $this->listener([])->onSaveAbsoluteUrl('', $this->dc()));
    }

    public function testReviewStandardWithoutReviewIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tl_grounding_page.error.reviewStandardWithoutReview');

        $this->listener(['reviewerName' => '', 'lastReviewed' => ''])->onSaveReviewStandard('2.0', $this->dc());
    }

    public function testConnectionIsRequired(): void
    {
        // Ohne Verbindung würde die Sektionsprüfung still nicht blockieren.
        $parameter = array_values(array_filter(
            (new \ReflectionMethod(ReferenceFieldsListener::class, '__construct'))->getParameters(),
            static fn (\ReflectionParameter $p): bool => 'connection' === $p->getName(),
        ))[0];

        $this->assertFalse($parameter->allowsNull());
        $this->assertFalse($parameter->isOptional());
    }

    public function testV2ParentBlocksOwnDomainSectionIdentifier(): void
    {
        $listener = $this->listener([], null, [], ['reviewStandard' => '2.0', 'entityId' => 'https://example.com/about/#organization', 'anchorUrl' => '']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tl_grounding_page.error.sameAs.ownDomain');

        $listener->onSaveSectionIdentifiers(serialize([['label' => 'Website', 'value' => 'https://example.com/press']]), $this->dc(7));
    }

    public function testUppercaseSchemeSectionIdentifierIsValidatedToo(): void
    {
        $listener = $this->listener([], null, [], ['reviewStandard' => '2.0', 'entityId' => 'https://example.com/about/#organization', 'anchorUrl' => '']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tl_grounding_page.error.sameAs.ownDomain');

        $listener->onSaveSectionIdentifiers(serialize([['label' => 'Website', 'value' => 'HTTPS://example.com/press']]), $this->dc(7));
    }

    public function testV17ParentOnlyAddsInfoForSectionIdentifier(): void
    {
        $message = $this->createAdapterMock(['addInfo']);
        $message
            ->expects($this->once())
            ->method('addInfo')
        ;

        $listener = $this->listener([], $message, [], ['reviewStandard' => '1.7', 'entityId' => '', 'anchorUrl' => '']);
        $value = serialize([['label' => 'DOI', 'value' => 'https://doi.org/10.1000/182']]);

        $this->assertSame($value, $listener->onSaveSectionIdentifiers($value, $this->dc(7)));
    }

    public function testNonUrlSectionIdentifiersAreIgnored(): void
    {
        $message = $this->createAdapterMock(['addInfo']);
        $message
            ->expects($this->never())
            ->method('addInfo')
        ;

        $listener = $this->listener([], $message, [], ['reviewStandard' => '2.0', 'entityId' => '', 'anchorUrl' => '']);
        $value = serialize([['label' => 'Mitgliedstaaten', 'value' => '25'], ['label' => 'Wikidata', 'value' => 'Q42944']]);

        $this->assertSame($value, $listener->onSaveSectionIdentifiers($value, $this->dc(7)));
    }

    /**
     * @param array<string, string>      $post
     * @param list<string>               $rootDns
     * @param array<string, string>|null $parent  Elterndatensatz aus tl_grounding_page
     */
    private function listener(array $post, object|null $message = null, array $rootDns = [], array|null $parent = null): ReferenceFieldsListener
    {
        $connection = $this->createStub(Connection::class);
        $connection
            ->method('fetchAssociative')
            ->willReturn($parent ?? false)
        ;

        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/contao', 'POST', $post));

        $translator = $this->createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturnCallback(static fn (string $id, array $params = []): string => $id.(isset($params[0]) ? ' '.$params[0] : ''))
        ;

        $hosts = $this->createStub(OwnHostsProvider::class);
        $hosts
            ->method('hosts')
            ->willReturnCallback(static fn (string ...$urls): array => [
                ...$rootDns,
                ...array_values(array_filter(array_map(SameAsPolicy::hostOf(...), $urls))),
            ])
        ;

        return new ReferenceFieldsListener(
            new SameAsPolicy(),
            new ReviewFieldsValidator(),
            new DcaSiblingReader($requestStack),
            $hosts,
            $translator,
            $this->createContaoFrameworkStub([Message::class => $message ?? $this->createAdapterStub(['addInfo'])]),
            $connection,
        );
    }

    private function dc(int $pid = 0): DataContainer
    {
        $dc = $this->createStub(DataContainer::class);
        $dc
            ->method('getCurrentRecord')
            ->willReturn($pid > 0 ? ['pid' => $pid] : [])
        ;

        return $dc;
    }
}
