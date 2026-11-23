<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Twig;

use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;
use Trassd\Contao\GroundingPages\Dto\GroundingSectionDto;
use Trassd\Contao\GroundingPages\Reference\ConformanceResult;
use Trassd\Contao\GroundingPages\Reference\EntityIdentity;

class OfficialProfilesTemplateTest extends TwigTemplateTestCase
{
    public function testEverySameAsAndIdentifierIsVisibleOnce(): void
    {
        $html = $this->renderPage($this->dto());

        $this->assertStringContainsString('grounding.detail.officialProfiles[CERN]', $html);
        $this->assertSame(1, substr_count($html, 'href="https://www.wikidata.org/wiki/Q42944"'));
        $this->assertSame(1, substr_count($html, 'href="https://ror.org/01ggx4157"'));
        $this->assertStringContainsString('<dt>Mitgliedstaaten</dt>', $html);
        $this->assertStringContainsString('<dd>25</dd>', $html);
    }

    public function testNonHttpSameAsIsNeverRenderedAsLink(): void
    {
        $html = $this->renderPage(new GroundingPageDto(name: 'CERN', schemaType: 'Organization', sameAs: ['javascript:alert(1)', 'foo', 'HTTPS://example.org/x']));

        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('href="foo"', $html);
        $this->assertStringContainsString('href="HTTPS://example.org/x"', $html);
    }

    public function testLabelledProfileShowsLabel(): void
    {
        $this->assertStringContainsString('ROR: <a href="https://ror.org/01ggx4157"', $this->renderPage($this->dto()));
    }

    public function testSourcesSectionNoLongerShowsIdentifiers(): void
    {
        $html = $this->render('@Contao/grounding/section/_sources.html.twig', [
            'section' => $this->dto()->sections[0],
            'name' => 'CERN',
        ]);

        $this->assertStringNotContainsString('Mitgliedstaaten', $html);
        $this->assertStringContainsString('grounding.detail.sourcesNamed[CERN]', $html);
        $this->assertStringContainsString('href="https://example.org/quelle"', $html);
    }

    public function testBlockIsOmittedWithoutProfilesAndIdentifiers(): void
    {
        $html = $this->renderPage(new GroundingPageDto(name: 'CERN', schemaType: 'Organization'));

        $this->assertStringNotContainsString('grounding-page__profiles', $html);
    }

    public function testNonAnchorPageLinksToAnchor(): void
    {
        $html = $this->renderPage($this->dto(), new EntityIdentity('https://example.com/facts/cern', 'https://example.com/about/#organization', 'https://example.com/about/', false));

        $this->assertMatchesRegularExpression('#class="grounding-page__anchor-link"><a href="https://example.com/about/">grounding.detail.anchorLink\[CERN\]</a>#', $html);
    }

    public function testAnchorPageHasNoAnchorLink(): void
    {
        $this->assertStringNotContainsString('grounding-page__anchor-link', $this->renderPage($this->dto()));
    }

    public function testDistinctionHeadingNamesTheEntity(): void
    {
        $html = $this->renderPage(new GroundingPageDto(name: 'CERN', schemaType: 'Organization', distinction: 'Nicht das Kernkraftwerk.'));

        $this->assertStringContainsString('grounding.detail.distinctionNamed[CERN]', $html);
    }

    public function testSectionDefaultHeadingsNameTheEntity(): void
    {
        $html = $this->renderPage(new GroundingPageDto(
            name: 'CERN',
            schemaType: 'Organization',
            changelog: [['date' => '2026-11-01', 'change' => 'Angelegt']],
            sections: [
                new GroundingSectionDto(sectionType: 'faq', faq: [['question' => 'F?', 'answer' => 'A.']]),
                new GroundingSectionDto(sectionType: 'defined-terms', definedTerms: [['term' => 'LHC', 'definition' => 'Beschleuniger']]),
                new GroundingSectionDto(sectionType: 'sources', sources: [['title' => 'Quelle', 'url' => 'https://example.org/quelle']]),
            ],
        ));

        $this->assertStringContainsString('<h2>grounding.detail.faqNamed[CERN]</h2>', $html);
        $this->assertStringContainsString('<h2>grounding.detail.definedTermsNamed[CERN]</h2>', $html);
        $this->assertStringContainsString('<h2>grounding.detail.sourcesNamed[CERN]</h2>', $html);
        $this->assertStringContainsString('<h2>grounding.detail.changelogNamed[CERN]</h2>', $html);
    }

    public function testSectionPartialsRenderWithoutName(): void
    {
        // Overrides von _page, die name nicht übergeben, dürfen nicht brechen.
        $html = $this->render('@Contao/grounding/section/_faq.html.twig', [
            'section' => new GroundingSectionDto(sectionType: 'faq', faq: [['question' => 'F?', 'answer' => 'A.']]),
        ]);

        $this->assertStringContainsString('grounding.detail.faqNamed[]', $html);
    }

    public function testFooterShowsResolvedV2Version(): void
    {
        $html = $this->renderPage($this->dto(), null, new ConformanceResult('2.0'));

        $this->assertStringContainsString('grounding.detail.standardLinkText[2.0]', $html);
    }

    public function testLegacyOverrideWithoutNewVariablesRendersAndStatesAtMostV17(): void
    {
        // Ein Projekt-Override von content_element/grounding_page ruft _page nur mit
        // grounding auf.
        $html = $this->render('@Contao/grounding/_page.html.twig', ['grounding' => $this->dto()]);

        $this->assertStringContainsString('grounding.detail.standardLinkText[1.7]', $html);
        $this->assertStringNotContainsString('grounding-page__anchor-link', $html);
    }

    private function dto(): GroundingPageDto
    {
        return new GroundingPageDto(
            name: 'CERN',
            schemaType: 'Organization',
            sameAs: ['https://www.wikidata.org/wiki/Q42944'],
            sections: [new GroundingSectionDto(
                sectionType: 'sources',
                sources: [['title' => 'Quelle', 'url' => 'https://example.org/quelle']],
                identifiers: [
                    ['label' => 'Wikidata', 'value' => 'https://www.wikidata.org/wiki/Q42944'],
                    ['label' => 'ROR', 'value' => 'https://ror.org/01ggx4157'],
                    ['label' => 'Mitgliedstaaten', 'value' => '25'],
                ],
            )],
            reviewerName: 'Jane Doe',
            lastReviewed: '2026-11-20',
            reviewStandard: '2.0',
        );
    }

    private function renderPage(GroundingPageDto $dto, EntityIdentity|null $identity = null, ConformanceResult|null $conformance = null): string
    {
        return $this->render('@Contao/grounding/_page.html.twig', [
            'grounding' => $dto,
            'identity' => $identity ?? new EntityIdentity('https://example.com/cern', 'https://example.com/cern#organization', 'https://example.com/cern', true),
            'conformance' => $conformance ?? new ConformanceResult('1.7'),
        ]);
    }
}
