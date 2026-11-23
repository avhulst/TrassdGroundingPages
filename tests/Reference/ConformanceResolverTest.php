<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Reference;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;
use Trassd\Contao\GroundingPages\Reference\ConformanceResolver;
use Trassd\Contao\GroundingPages\Reference\ConformanceResult;
use Trassd\Contao\GroundingPages\Reference\EntityIdentity;
use Trassd\Contao\GroundingPages\Reference\SameAsPolicy;

class ConformanceResolverTest extends TestCase
{
    public function testUnreviewedPageIsBaseVersion(): void
    {
        $result = $this->resolve(new GroundingPageDto(name: 'CERN', schemaType: 'Organization', reviewStandard: '2.0'));

        $this->assertSame('1.6.1', $result->version);
        $this->assertFalse($result->isV2());
    }

    public function testReviewedPageWithoutStandardStaysOnV17(): void
    {
        $this->assertSame('1.7', $this->resolve($this->reviewed(''))->version);
    }

    public function testExplicitV17StaysOnV17(): void
    {
        $this->assertSame('1.7', $this->resolve($this->reviewed('1.7'))->version);
    }

    public function testValidV2PageIsV2(): void
    {
        $result = $this->resolve($this->reviewed('2.0', ['https://www.wikidata.org/wiki/Q42944']));

        $this->assertSame('2.0', $result->version);
        $this->assertTrue($result->isV2());
        $this->assertSame([], $result->violations);
    }

    public function testOwnDomainSameAsDowngradesAndLogs(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('warning')
        ;

        $result = $this->resolve($this->reviewed('2.0', ['https://www.example.com/about/']), $logger);

        $this->assertSame('1.7', $result->version);
        $this->assertSame(['sameAs:ownDomain:https://www.example.com/about/'], $result->violations);
    }

    public function testAnchorHostCountsAsOwnDomain(): void
    {
        $identity = new EntityIdentity('https://facts.example.net/cern', 'https://example.com/about/#organization', 'https://example.com/about/', false);

        $result = (new ConformanceResolver(new SameAsPolicy()))->resolve($this->reviewed('2.0', ['https://example.com/press']), $identity);

        $this->assertSame('1.7', $result->version);
    }

    public function testInternationalisedSameAsStaysV2(): void
    {
        $result = $this->resolve($this->reviewed('2.0', ['https://de.wikipedia.org/wiki/Müller']));

        $this->assertSame('2.0', $result->version);
        $this->assertSame([], $result->violations);
    }

    public function testNonHttpSameAsDowngradesEvenThoughProfilesDropIt(): void
    {
        $result = $this->resolve($this->reviewed('2.0', ['foo']));

        $this->assertSame('1.7', $result->version);
        $this->assertSame(['sameAs:notHttp:foo'], $result->violations);
    }

    public function testRelativeEntityIdDowngrades(): void
    {
        $identity = new EntityIdentity('https://example.com/cern', '#organization', 'https://example.com/cern', true);

        $result = (new ConformanceResolver(new SameAsPolicy()))->resolve($this->reviewed('2.0'), $identity);

        $this->assertSame('1.7', $result->version);
        $this->assertSame(['entityId'], $result->violations);
    }

    public function testInvalidStoredEntityIdDowngradesEvenThoughIdentityFallsBack(): void
    {
        $dto = new GroundingPageDto(name: 'CERN', schemaType: 'Organization', reviewerName: 'Jane Doe', lastReviewed: '2026-11-20', reviewStandard: '2.0', entityId: 'javascript:alert(1)');

        $result = $this->resolve($dto);

        $this->assertSame('1.7', $result->version);
        $this->assertSame(['entityId'], $result->violations);
    }

    public function testInvalidStoredAnchorUrlDowngrades(): void
    {
        $dto = new GroundingPageDto(name: 'CERN', schemaType: 'Organization', reviewerName: 'Jane Doe', lastReviewed: '2026-11-20', reviewStandard: '2.0', anchorUrl: '/about/');

        $result = $this->resolve($dto);

        $this->assertSame('1.7', $result->version);
        $this->assertSame(['anchorUrl'], $result->violations);
    }

    public function testRelativeStoredAndResolvedEntityIdIsReportedOnce(): void
    {
        $dto = new GroundingPageDto(name: 'CERN', schemaType: 'Organization', reviewerName: 'Jane Doe', lastReviewed: '2026-11-20', reviewStandard: '2.0', entityId: '#organization');
        $identity = new EntityIdentity('https://example.com/cern', '#organization', 'https://example.com/cern', true);

        $result = (new ConformanceResolver(new SameAsPolicy()))->resolve($dto, $identity);

        $this->assertSame(['entityId'], $result->violations);
    }

    public function testSameFailingPageIsLoggedOnlyOnceWithCache(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('warning')
        ;

        $resolver = new ConformanceResolver(new SameAsPolicy(), $logger, new ArrayAdapter());
        $identity = new EntityIdentity('https://example.com/cern', 'https://example.com/cern#organization', 'https://example.com/cern', true);
        $dto = $this->reviewed('2.0', ['https://www.example.com/about/']);

        $this->assertSame('1.7', $resolver->resolve($dto, $identity)->version);
        $this->assertSame('1.7', $resolver->resolve($dto, $identity)->version);
    }

    public function testChangedViolationsAreLoggedAgain(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->exactly(2))
            ->method('warning')
        ;

        $resolver = new ConformanceResolver(new SameAsPolicy(), $logger, new ArrayAdapter());
        $identity = new EntityIdentity('https://example.com/cern', 'https://example.com/cern#organization', 'https://example.com/cern', true);

        $resolver->resolve($this->reviewed('2.0', ['https://www.example.com/about/']), $identity);
        $resolver->resolve($this->reviewed('2.0', ['foo']), $identity);
    }

    public function testWithoutCacheEveryRenderLogs(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->exactly(2))
            ->method('warning')
        ;

        $dto = $this->reviewed('2.0', ['foo']);
        $this->resolve($dto, $logger);
        $this->resolve($dto, $logger);
    }

    /**
     * @param list<string> $sameAs
     */
    private function reviewed(string $standard, array $sameAs = []): GroundingPageDto
    {
        return new GroundingPageDto(
            name: 'CERN',
            schemaType: 'Organization',
            sameAs: $sameAs,
            reviewerName: 'Jane Doe',
            lastReviewed: '2026-11-20',
            reviewStandard: $standard,
        );
    }

    private function resolve(GroundingPageDto $dto, LoggerInterface|null $logger = null): ConformanceResult
    {
        $identity = new EntityIdentity('https://example.com/cern', 'https://example.com/cern#organization', 'https://example.com/cern', true);

        return (new ConformanceResolver(new SameAsPolicy(), $logger))->resolve($dto, $identity);
    }
}
