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
    case FURTHER_READING = 'further-reading';

    public static function fromStringOrFallback(string $value): self
    {
        return self::tryFrom($value) ?? self::FACT_GRID;
    }

    /**
     * Die rowWizard-Felder, die dieser Sektionstyp per Subpalette freischaltet.
     * SOURCES trägt zwei Felder (Quellen + Identifikatoren).
     *
     * @return list<SectionRowSchema>
     */
    public function fields(): array
    {
        return match ($this) {
            self::FACT_GRID => [SectionRowSchema::FactGrid],
            self::TIMELINE => [SectionRowSchema::Timeline],
            self::DEFINED_TERMS => [SectionRowSchema::DefinedTerms],
            self::FAQ => [SectionRowSchema::Faq],
            self::SOURCES => [SectionRowSchema::Sources, SectionRowSchema::Identifiers],
            self::FURTHER_READING => [SectionRowSchema::FurtherReading],
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
