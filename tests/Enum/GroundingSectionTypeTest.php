<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\Enum\GroundingSectionType;

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
}
