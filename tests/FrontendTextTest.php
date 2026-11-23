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

        // P-3: Bundle-Templates nutzen die *Named-Schlüssel mit Entitätsnamen.
        foreach (['distinctionNamed', 'sourcesNamed', 'changelogNamed', 'definedTermsNamed', 'faqNamed', 'officialProfiles', 'anchorLink'] as $key) {
            $this->assertStringContainsString('%s', $GLOBALS['TL_LANG']['grounding']['detail'][$key], $key);
        }

        // Alte Schlüssel bleiben unverändert und ohne Platzhalter, damit Overrides mit
        // trans([]) keinen wörtlichen "%s" ausgeben.
        foreach (['distinction', 'sources', 'changelog', 'definedTerms', 'faq'] as $key) {
            $this->assertStringNotContainsString('%', $GLOBALS['TL_LANG']['grounding']['detail'][$key], $key);
        }
        // Contao-Translator nutzt vsprintf → positionale Specifier (%1$s), keine
        // %name%-Platzhalter.
        $this->assertStringContainsString('%1$s', $GLOBALS['TL_LANG']['grounding']['detail']['segmentAssignment']);
        $this->assertStringContainsString('%1$s', $GLOBALS['TL_LANG']['grounding']['detail']['standardText']);
        $this->assertStringContainsString('%3$s', $GLOBALS['TL_LANG']['grounding']['detail']['standardTextUpdated']);
        // Alter Schlüssel bleibt für überschriebene Templates bestehen, mit neuem Sinn.
        $this->assertSame(
            $GLOBALS['TL_LANG']['grounding']['detail']['standardTextUpdated'],
            $GLOBALS['TL_LANG']['grounding']['detail']['standardTextVerified'],
        );
        $this->assertStringContainsString('%1$s', $GLOBALS['TL_LANG']['grounding']['detail']['furtherReading']);
        $this->assertStringContainsString('%1$s', $GLOBALS['TL_LANG']['grounding']['review']['notice']);
        $this->assertStringContainsString('%2$s', $GLOBALS['TL_LANG']['grounding']['review']['notice']);
        $this->assertNotEmpty($GLOBALS['TL_LANG']['grounding']['humanNotice']['label']);
        $this->assertNotEmpty($GLOBALS['TL_LANG']['CTE']['grounding_page'][0]);
    }

    public function testLegacyHeadingStringsAreUnchanged(): void
    {
        unset($GLOBALS['TL_LANG']);
        include __DIR__.'/../contao/languages/de/default.php';
        $this->assertSame('Abgrenzung', $GLOBALS['TL_LANG']['grounding']['detail']['distinction']);
        $this->assertSame('Quellen & Vertrauenssignale', $GLOBALS['TL_LANG']['grounding']['detail']['sources']);
        $this->assertSame('Änderungsprotokoll', $GLOBALS['TL_LANG']['grounding']['detail']['changelog']);

        unset($GLOBALS['TL_LANG']);
        include __DIR__.'/../contao/languages/en/default.php';
        $this->assertSame('Distinction', $GLOBALS['TL_LANG']['grounding']['detail']['distinction']);
        $this->assertSame('Sources & Trust Signals', $GLOBALS['TL_LANG']['grounding']['detail']['sources']);
        $this->assertSame('Changelog', $GLOBALS['TL_LANG']['grounding']['detail']['changelog']);
    }
}
