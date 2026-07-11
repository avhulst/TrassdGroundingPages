<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Enum;

/*
 * Einziger Owner des Spalten-Vokabulars eines rowWizard-Felds einer Grounding-Section.
 * Der Backing-Value ist der Model-/DCA-Feldname; columns() liefert das Spaltenpaar,
 * nach dem rowWizard jede Zeile assoziativ serialisiert.
 *
 * DCA (contao/dca/tl_grounding_section.php), GroundingPageComposer und
 * GroundingJsonLdBuilder leiten ihre Row-Keys hieraus ab, damit die Spalten
 * genau eine Wahrheit haben. Reine Daten, keine Framework-Kopplung.
 */
enum SectionRowSchema: string
{
    case FactGrid = 'factGrid';
    case Timeline = 'timelineItems';
    case DefinedTerms = 'definedTerms';
    case Faq = 'faqItems';
    case Sources = 'sources';
    case Identifiers = 'identifiers';

    public function fieldName(): string
    {
        return $this->value;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function columns(): array
    {
        return match ($this) {
            self::FactGrid, self::Identifiers => ['label', 'value'],
            self::Timeline => ['year', 'event'],
            self::DefinedTerms => ['term', 'definition'],
            self::Faq => ['question', 'answer'],
            self::Sources => ['title', 'url'],
        };
    }
}
