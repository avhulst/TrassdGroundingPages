<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\Enum\GroundingSectionType;
use Trassd\Contao\GroundingPages\Enum\SectionRowSchema;

class GroundingSectionTypeTest extends TestCase
{
    public function testValuesAreExactlyTheFiveSectionTypes(): void
    {
        $this->assertSame(
            ['fact-grid', 'timeline', 'defined-terms', 'faq', 'sources'],
            GroundingSectionType::values(),
        );
    }

    public function testEntityHeaderIsNotASectionType(): void
    {
        $this->assertNotContains('entity-header', GroundingSectionType::values());
        $this->assertNull(GroundingSectionType::tryFrom('entity-header'));
    }

    public function testFromStringOrFallbackResolvesKnownValue(): void
    {
        $this->assertSame(GroundingSectionType::FAQ, GroundingSectionType::fromStringOrFallback('faq'));
    }

    public function testFromStringOrFallbackFallsBackToFactGrid(): void
    {
        $this->assertSame(
            GroundingSectionType::FACT_GRID,
            GroundingSectionType::fromStringOrFallback('entity-header'),
        );
    }

    public function testSingleFieldTypesMapToTheirOneRowSchema(): void
    {
        $this->assertSame([SectionRowSchema::FactGrid], GroundingSectionType::FACT_GRID->fields());
        $this->assertSame([SectionRowSchema::Timeline], GroundingSectionType::TIMELINE->fields());
        $this->assertSame([SectionRowSchema::DefinedTerms], GroundingSectionType::DEFINED_TERMS->fields());
        $this->assertSame([SectionRowSchema::Faq], GroundingSectionType::FAQ->fields());
    }

    public function testSourcesCarriesBothSourcesAndIdentifiers(): void
    {
        $this->assertSame(
            [SectionRowSchema::Sources, SectionRowSchema::Identifiers],
            GroundingSectionType::SOURCES->fields(),
        );
    }

    public function testEveryTypeHasAtLeastOneField(): void
    {
        foreach (GroundingSectionType::cases() as $type) {
            $this->assertNotSame([], $type->fields(), $type->name);
        }
    }
}
