<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Dca;

use Contao\DataContainer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TlGroundingSectionDcaTest extends TestCase
{
    public function testIsChildTableSortedManually(): void
    {
        $dca = $this->loadDca();

        $this->assertSame('tl_grounding_page', $dca['config']['ptable']);
        $this->assertSame(DataContainer::MODE_PARENT, $dca['list']['sorting']['mode']);
        $this->assertSame(['sorting'], $dca['list']['sorting']['fields']);
    }

    public function testSectionTypeIsSubmitOnChangeSelectorWithFiveOptions(): void
    {
        $dca = $this->loadDca();
        $field = $dca['fields']['sectionType'];

        $this->assertSame('select', $field['inputType']);
        $this->assertTrue($field['eval']['submitOnChange']);
        $this->assertContains('sectionType', $dca['palettes']['__selector__']);
        $this->assertSame(['fact-grid', 'timeline', 'defined-terms', 'faq', 'sources'], $field['options']);
    }

    /**
     * Der Subpalette-Key eines select-Selektors folgt dem Muster fieldName_fieldValue.
     *
     * @return iterable<string, array{string, string}>
     */
    public static function subpaletteProvider(): iterable
    {
        yield 'fact-grid' => ['sectionType_fact-grid', 'factGrid'];
        yield 'timeline' => ['sectionType_timeline', 'timelineItems'];
        yield 'defined-terms' => ['sectionType_defined-terms', 'definedTerms'];
        yield 'faq' => ['sectionType_faq', 'faqItems'];
        yield 'sources' => ['sectionType_sources', 'sources,identifiers'];
    }

    #[DataProvider('subpaletteProvider')]
    public function testSubpaletteSwitchesTheRightFields(string $key, string $expected): void
    {
        $dca = $this->loadDca();

        $this->assertArrayHasKey($key, $dca['subpalettes']);
        $this->assertSame($expected, $dca['subpalettes'][$key]);
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function rowWizardProvider(): iterable
    {
        yield 'factGrid' => ['factGrid', 'label', 'value'];
        yield 'timelineItems' => ['timelineItems', 'year', 'event'];
        yield 'definedTerms' => ['definedTerms', 'term', 'definition'];
        yield 'faqItems' => ['faqItems', 'question', 'answer'];
        yield 'sources' => ['sources', 'title', 'url'];
        yield 'identifiers' => ['identifiers', 'label', 'value'];
    }

    #[DataProvider('rowWizardProvider')]
    public function testRowWizardFieldsAreNamedTwoColumnBlobs(string $field, string $colA, string $colB): void
    {
        $dca = $this->loadDca();
        $definition = $dca['fields'][$field];

        $this->assertSame('rowWizard', $definition['inputType']);
        $this->assertArrayHasKey($colA, $definition['fields']);
        $this->assertArrayHasKey($colB, $definition['fields']);
        $this->assertSame('blob', $definition['sql']['type']);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadDca(): array
    {
        unset($GLOBALS['TL_DCA']['tl_grounding_section']);

        include __DIR__.'/../../contao/dca/tl_grounding_section.php';

        return $GLOBALS['TL_DCA']['tl_grounding_section'];
    }
}
