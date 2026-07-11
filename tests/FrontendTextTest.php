<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FrontendTextTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function languageProvider(): iterable
    {
        yield 'de' => ['de'];
        yield 'en' => ['en'];
    }

    #[DataProvider('languageProvider')]
    public function testDefaultFileProvidesGroundingTypeAndElementLabels(string $language): void
    {
        unset($GLOBALS['TL_LANG']);

        include __DIR__.'/../contao/languages/'.$language.'/default.php';

        $this->assertNotEmpty($GLOBALS['TL_LANG']['grounding']['detail']['distinction']);
        $this->assertNotEmpty($GLOBALS['TL_LANG']['grounding']['detail']['faq']);
        // Contao-Translator nutzt vsprintf → positionale Specifier (%1$s), keine
        // %name%-Platzhalter.
        $this->assertStringContainsString('%1$s', $GLOBALS['TL_LANG']['grounding']['detail']['segmentAssignment']);
        $this->assertStringContainsString('%1$s', $GLOBALS['TL_LANG']['grounding']['detail']['standardText']);
        $this->assertNotEmpty($GLOBALS['TL_LANG']['grounding']['humanNotice']['label']);
        $this->assertNotEmpty($GLOBALS['TL_LANG']['CTE']['grounding_page'][0]);
    }
}
