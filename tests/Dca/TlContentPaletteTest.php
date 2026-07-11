<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Dca;

use PHPUnit\Framework\TestCase;

class TlContentPaletteTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['TL_DCA']['tl_content']);
    }

    public function testGroundingPagePaletteExistsAndContainsSelectField(): void
    {
        $dca = $this->loadDca();

        $this->assertArrayHasKey('grounding_page', $dca['palettes']);
        $this->assertStringContainsString('groundingPage', $dca['palettes']['grounding_page']);
    }

    public function testGroundingPageFieldIsForeignKeySelectWithColumn(): void
    {
        $field = $this->loadDca()['fields']['groundingPage'];

        $this->assertSame('select', $field['inputType']);
        $this->assertSame('tl_grounding_page.title', $field['foreignKey']);
        $this->assertTrue($field['eval']['mandatory']);
        $this->assertArrayHasKey('sql', $field);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadDca(): array
    {
        include __DIR__.'/../../contao/dca/tl_content.php';

        return $GLOBALS['TL_DCA']['tl_content'];
    }
}
