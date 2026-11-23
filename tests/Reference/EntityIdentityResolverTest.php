<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Reference;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;
use Trassd\Contao\GroundingPages\Reference\EntityIdentityResolver;

class EntityIdentityResolverTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function fragmentProvider(): iterable
    {
        yield 'organization' => ['Organization', '#organization'];
        yield 'person' => ['Person', '#person'];
        yield 'software' => ['SoftwareApplication', '#softwareapplication'];
        yield 'thing' => ['Thing', '#entity'];
        yield 'empty' => ['', '#entity'];
    }

    #[DataProvider('fragmentProvider')]
    public function testFragmentFor(string $schemaType, string $expected): void
    {
        $this->assertSame($expected, EntityIdentityResolver::fragmentFor($schemaType));
    }

    public function testSinglePageIsItsOwnAnchorWithAutomaticIdentifier(): void
    {
        $identity = (new EntityIdentityResolver())->resolve(
            new GroundingPageDto(name: 'CERN', schemaType: 'Organization'),
            'https://example.com/cern',
        );

        $this->assertSame('https://example.com/cern', $identity->pageUrl);
        $this->assertSame('https://example.com/cern#organization', $identity->entityId);
        $this->assertSame('https://example.com/cern', $identity->anchorUrl);
        $this->assertTrue($identity->isAnchor);
    }

    public function testQueryAndFragmentAreStrippedFromPageUrl(): void
    {
        $identity = (new EntityIdentityResolver())->resolve(
            new GroundingPageDto(name: 'CERN', schemaType: 'Organization'),
            'https://example.com/cern?page_n=2#faq',
        );

        $this->assertSame('https://example.com/cern', $identity->pageUrl);
        $this->assertSame('https://example.com/cern#organization', $identity->entityId);
    }

    public function testExplicitEntityIdWins(): void
    {
        $identity = (new EntityIdentityResolver())->resolve(
            new GroundingPageDto(name: 'CERN', schemaType: 'Organization', entityId: 'https://example.com/about/#org'),
            'https://example.com/cern',
        );

        $this->assertSame('https://example.com/about/#org', $identity->entityId);
    }

    public function testNonAnchorPageDerivesIdentifierFromAnchor(): void
    {
        $identity = (new EntityIdentityResolver())->resolve(
            new GroundingPageDto(name: 'CERN', schemaType: 'Organization', anchorUrl: 'https://example.com/about/'),
            'https://example.com/facts/cern',
        );

        $this->assertSame('https://example.com/facts/cern', $identity->pageUrl);
        $this->assertSame('https://example.com/about/', $identity->anchorUrl);
        $this->assertSame('https://example.com/about/#organization', $identity->entityId);
        $this->assertFalse($identity->isAnchor);
    }

    public function testAnchorUrlEqualToPageUrlStillCountsAsAnchor(): void
    {
        $identity = (new EntityIdentityResolver())->resolve(
            new GroundingPageDto(name: 'CERN', schemaType: 'Organization', anchorUrl: 'https://example.com/cern'),
            'https://example.com/cern',
        );

        $this->assertTrue($identity->isAnchor);
    }
}
