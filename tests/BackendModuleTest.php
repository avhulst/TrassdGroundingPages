<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BackendModuleTest extends TestCase
{
    public function testConfigRegistersBackendModuleAndModels(): void
    {
        unset($GLOBALS['BE_MOD'], $GLOBALS['TL_MODELS']);

        include __DIR__.'/../contao/config/config.php';

        $this->assertSame(
            ['tl_grounding_page', 'tl_grounding_section'],
            $GLOBALS['BE_MOD']['content']['grounding_pages']['tables'],
        );

        $this->assertSame('Contao\GroundingPageModel', $GLOBALS['TL_MODELS']['tl_grounding_page']);
        $this->assertSame('Contao\GroundingSectionModel', $GLOBALS['TL_MODELS']['tl_grounding_section']);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function languageProvider(): iterable
    {
        yield 'de' => ['de'];
        yield 'en' => ['en'];
    }

    #[DataProvider('languageProvider')]
    public function testLanguageFilesProvideLabels(string $language): void
    {
        unset($GLOBALS['TL_LANG']);

        include __DIR__.'/../contao/languages/'.$language.'/modules.php';
        include __DIR__.'/../contao/languages/'.$language.'/tl_grounding_page.php';
        include __DIR__.'/../contao/languages/'.$language.'/tl_grounding_section.php';

        $this->assertNotEmpty($GLOBALS['TL_LANG']['MOD']['grounding_pages'][0]);
        $this->assertNotEmpty($GLOBALS['TL_LANG']['tl_grounding_page']['title'][0]);
        $this->assertNotEmpty($GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['organization']);
        $this->assertNotEmpty($GLOBALS['TL_LANG']['tl_grounding_section']['sectionType']['fact-grid']);
        $this->assertNotEmpty($GLOBALS['TL_LANG']['tl_grounding_section']['col_year']);
    }
}
