<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Dto;

final readonly class GroundingSectionDto
{
    /**
     * Jede Zeile ist eine assoziative Bezeichnung→Wert-Abbildung
     * (rowWizard-Serialisierung): factGrid label/value, timeline year/event,
     * definedTerms term/definition, faq question/answer, sources title/url,
     * identifiers label/value.
     *
     * @param list<array<string, string>> $factGrid
     * @param list<array<string, string>> $timeline
     * @param list<array<string, string>> $definedTerms
     * @param list<array<string, string>> $faq
     * @param list<array<string, string>> $sources
     * @param list<array<string, string>> $identifiers
     */
    public function __construct(
        public string $sectionType,
        public string $sectionTitle = '',
        public array $factGrid = [],
        public array $timeline = [],
        public array $definedTerms = [],
        public array $faq = [],
        public array $sources = [],
        public array $identifiers = [],
    ) {
    }
}
