<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\JsonLd;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\JsonLd\ClassificationPropertyMap;

class ClassificationPropertyMapTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string|null, string|null}>
     */
    public static function typeProvider(): iterable
    {
        yield 'Organization' => ['Organization', 'areaServed', 'parentOrganization'];
        yield 'Service' => ['Service', 'areaServed', null];
        yield 'CreativeWork' => ['CreativeWork', 'spatialCoverage', 'isPartOf'];
        yield 'Dataset' => ['Dataset', 'spatialCoverage', 'isPartOf'];
        yield 'Person' => ['Person', null, null];
        yield 'Thing' => ['Thing', null, null];
        yield 'Place' => ['Place', null, null];
        yield 'Product' => ['Product', null, null];
    }

    #[DataProvider('typeProvider')]
    public function testResolvesTypeSpecificPropertiesOrFallback(string $type, string|null $geo, string|null $parent): void
    {
        $this->assertSame(
            ['geographicScope' => $geo, 'parentEntity' => $parent],
            ClassificationPropertyMap::resolve($type, false),
        );
    }

    public function testCustomTypeAlwaysFallsBack(): void
    {
        $this->assertSame(
            ['geographicScope' => null, 'parentEntity' => null],
            ClassificationPropertyMap::resolve('Organization', true),
        );
    }
}
