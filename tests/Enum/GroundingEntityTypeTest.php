<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Enum;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\Enum\GroundingEntityType;

class GroundingEntityTypeTest extends TestCase
{
    public function testCoversAllEighteenClasses(): void
    {
        $this->assertCount(18, GroundingEntityType::cases());
    }

    #[DataProvider('schemaTypeProvider')]
    public function testSchemaTypeMapping(GroundingEntityType $type, string $expected): void
    {
        $this->assertSame($expected, $type->schemaType());
    }

    /**
     * @return iterable<string, array{GroundingEntityType, string}>
     */
    public static function schemaTypeProvider(): iterable
    {
        yield 'organization' => [GroundingEntityType::ORGANIZATION, 'Organization'];
        yield 'group-or-role' => [GroundingEntityType::GROUP_OR_ROLE, 'Organization'];
        yield 'person' => [GroundingEntityType::PERSON, 'Person'];
        yield 'product' => [GroundingEntityType::PRODUCT, 'Product'];
        yield 'service' => [GroundingEntityType::SERVICE, 'Service'];
        yield 'tool-or-platform' => [GroundingEntityType::TOOL_OR_PLATFORM, 'SoftwareApplication'];
        yield 'field-of-knowledge' => [GroundingEntityType::FIELD_OF_KNOWLEDGE, 'DefinedTerm'];
        yield 'concept' => [GroundingEntityType::CONCEPT, 'DefinedTerm'];
        yield 'publication' => [GroundingEntityType::PUBLICATION, 'CreativeWork'];
        yield 'standard' => [GroundingEntityType::STANDARD, 'CreativeWork'];
        yield 'dataset' => [GroundingEntityType::DATASET, 'Dataset'];
        yield 'method' => [GroundingEntityType::METHOD, 'HowTo'];
        yield 'place' => [GroundingEntityType::PLACE, 'Place'];
        yield 'event' => [GroundingEntityType::EVENT, 'Event'];
        yield 'project' => [GroundingEntityType::PROJECT, 'Project'];
        yield 'feature' => [GroundingEntityType::FEATURE, 'Thing'];
        yield 'segment' => [GroundingEntityType::SEGMENT, 'Thing'];
        yield 'metric' => [GroundingEntityType::METRIC, 'Thing'];
    }

    public function testEverySchemaTypeIsNonEmpty(): void
    {
        foreach (GroundingEntityType::cases() as $case) {
            $this->assertNotSame('', $case->schemaType());
        }
    }

    public function testFromStringOrFallbackResolvesKnownValue(): void
    {
        $this->assertSame(GroundingEntityType::PERSON, GroundingEntityType::fromStringOrFallback('person'));
    }

    public function testFromStringOrFallbackFallsBackToSegment(): void
    {
        $fallback = GroundingEntityType::fromStringOrFallback('does-not-exist');

        $this->assertSame(GroundingEntityType::SEGMENT, $fallback);
        $this->assertSame('Thing', $fallback->schemaType());
    }

    public function testOptionsContainAllEighteenValues(): void
    {
        $options = GroundingEntityType::options();

        $this->assertCount(18, $options);
        $this->assertSame('organization', $options['organization']);
        $this->assertArrayHasKey('tool-or-platform', $options);

        foreach ($options as $key => $value) {
            $this->assertSame($key, $value);
        }
    }
}
