<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Enum;

enum GroundingSectionType: string
{
    case FACT_GRID = 'fact-grid';
    case TIMELINE = 'timeline';
    case DEFINED_TERMS = 'defined-terms';
    case FAQ = 'faq';
    case SOURCES = 'sources';

    public static function fromStringOrFallback(string $value): self
    {
        return self::tryFrom($value) ?? self::FACT_GRID;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
