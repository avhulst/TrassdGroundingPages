<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Twig;

use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;

class EntityHeaderClassificationTemplateTest extends TwigTemplateTestCase
{
    public function testRendersClassificationInMetaList(): void
    {
        $html = $this->header(new GroundingPageDto(
            name: 'CERN',
            schemaType: 'Organization',
            geographicScope: 'International',
            parentEntity: 'UNESCO',
            parentEntityUrl: 'https://www.unesco.org/',
            relationships: [
                ['relation' => 'Betreibt', 'name' => 'LHC', 'url' => 'https://home.cern/lhc'],
                ['relation' => 'Mitglied von', 'name' => 'EIROforum', 'url' => ''],
            ],
        ));

        $this->assertStringContainsString('<dt>grounding.detail.geographicScope</dt>', $html);
        $this->assertStringContainsString('<dd>International</dd>', $html);
        $this->assertStringContainsString('<a href="https://www.unesco.org/" rel="noopener">UNESCO</a>', $html);
        $this->assertStringContainsString('<dt>grounding.detail.relationships</dt>', $html);
        $this->assertStringContainsString('Betreibt: <a href="https://home.cern/lhc" rel="noopener">LHC</a>', $html);
        $this->assertStringContainsString('<li>Mitglied von: EIROforum</li>', $html);
    }

    /**
     * Das Meta-Raster hat zwei Spalten: Jedes <dt> braucht genau ein <dd>, sonst
     * verrutschen die folgenden Zeilen. Mehrere Beziehungen stehen als Liste in einem <dd>.
     */
    public function testEveryTermHasExactlyOneDescriptionWithSeveralRelationships(): void
    {
        $html = $this->header(new GroundingPageDto(
            name: 'X',
            schemaType: 'Organization',
            publisher: 'Herausgeber',
            relationships: [
                ['relation' => 'Mitglied von', 'name' => 'A', 'url' => ''],
                ['relation' => 'Hersteller von', 'name' => 'B', 'url' => 'https://example.com/b'],
                ['relation' => 'Hersteller von', 'name' => 'C', 'url' => ''],
            ],
            dateModified: '2026-10-20',
        ));

        $this->assertSame(substr_count($html, '<dt>'), substr_count($html, '<dd>'));
        $this->assertSame(3, substr_count($html, '<li>'));
        $this->assertMatchesRegularExpression(
            '#<dt>grounding\.detail\.relationships</dt>\s*<dd>\s*<ul class="grounding-page__relationships">#',
            $html,
        );
    }

    public function testMetaListAppearsWithOnlyAClassificationValue(): void
    {
        $html = $this->header(new GroundingPageDto(name: 'X', schemaType: 'Thing', geographicScope: 'DACH'));

        $this->assertStringContainsString('grounding-page__meta', $html);
    }

    public function testNoClassificationRowsWhenEmpty(): void
    {
        $html = $this->header(new GroundingPageDto(name: 'X', schemaType: 'Thing'));

        $this->assertStringNotContainsString('grounding.detail.geographicScope', $html);
        $this->assertStringNotContainsString('grounding.detail.parentEntity', $html);
        $this->assertStringNotContainsString('grounding.detail.relationships', $html);
        $this->assertStringNotContainsString('grounding-page__meta', $html);
    }

    public function testUnsafeUrlsAreRenderedAsPlainText(): void
    {
        $html = $this->header(new GroundingPageDto(
            name: 'X',
            schemaType: 'Organization',
            parentEntity: 'Mutter',
            parentEntityUrl: 'javascript:alert(1)',
            relationships: [['relation' => 'Partner', 'name' => 'Y', 'url' => 'javascript:alert(2)']],
        ));

        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('<dd>Mutter</dd>', $html);
        $this->assertStringContainsString('<li>Partner: Y</li>', $html);
    }

    private function header(GroundingPageDto $dto): string
    {
        return $this->render('@Contao/grounding/section/_entity_header.html.twig', ['grounding' => $dto]);
    }
}
