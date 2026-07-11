<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Dca;

use Contao\DC_Table;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TlGroundingPageDcaTest extends TestCase
{
    public function testUsesDcTableWithChildTableAndAliasIndex(): void
    {
        $dca = $this->loadDca();

        $this->assertSame(DC_Table::class, $dca['config']['dataContainer']);
        $this->assertSame(['tl_grounding_section'], $dca['config']['ctable']);
        $this->assertSame('index', $dca['config']['sql']['keys']['alias']);
    }

    public function testTitleIsMandatoryAndAliasUnique(): void
    {
        $dca = $this->loadDca();

        $this->assertTrue($dca['fields']['title']['eval']['mandatory']);
        $this->assertTrue($dca['fields']['alias']['eval']['unique']);
    }

    public function testEntityTypeSelectExposesAllEighteenOntologyValues(): void
    {
        $dca = $this->loadDca();
        $field = $dca['fields']['entityType'];

        $this->assertSame('select', $field['inputType']);
        $this->assertCount(18, $field['options']);
        $this->assertContains('organization', $field['options']);
        $this->assertContains('tool-or-platform', $field['options']);
    }

    /**
     * Jedes Feld muss eine sql-Definition tragen, damit contao:migrate die Spalte anlegt.
     *
     * @return iterable<string, array{string}>
     */
    public static function persistedFieldProvider(): iterable
    {
        foreach ([
            'title', 'alias', 'language', 'entityType', 'customSchemaType', 'definition',
            'segment', 'distinction', 'sameAs', 'publisher', 'maintainer', 'status',
            'entryVersion', 'datePublished', 'dateVerified', 'changelog', 'correctionContact', 'published',
        ] as $field) {
            yield $field => [$field];
        }
    }

    #[DataProvider('persistedFieldProvider')]
    public function testEveryEditableFieldHasSqlDefinition(string $field): void
    {
        $dca = $this->loadDca();

        $this->assertArrayHasKey($field, $dca['fields']);
        $this->assertArrayHasKey('sql', $dca['fields'][$field]);
    }

    public function testPaletteContainsGovernanceLegend(): void
    {
        $dca = $this->loadDca();

        $this->assertStringContainsString('{governance_legend}', $dca['palettes']['default']);
        $this->assertStringContainsString('publisher', $dca['palettes']['default']);
        $this->assertStringContainsString('changelog', $dca['palettes']['default']);
        $this->assertStringContainsString('correctionContact', $dca['palettes']['default']);
    }

    public function testChangelogIsNamedTwoColumnRowWizardBlob(): void
    {
        $field = $this->loadDca()['fields']['changelog'];

        $this->assertSame('rowWizard', $field['inputType']);
        $this->assertArrayHasKey('date', $field['fields']);
        $this->assertArrayHasKey('change', $field['fields']);
        $this->assertSame('blob', $field['sql']['type']);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadDca(): array
    {
        unset($GLOBALS['TL_DCA']['tl_grounding_page']);

        include __DIR__.'/../../contao/dca/tl_grounding_page.php';

        return $GLOBALS['TL_DCA']['tl_grounding_page'];
    }
}
