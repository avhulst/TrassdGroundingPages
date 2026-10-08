<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\JsonLd;

use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;
use Trassd\Contao\GroundingPages\Dto\GroundingSectionDto;
use Trassd\Contao\GroundingPages\JsonLd\GroundingJsonLdBuilder;

class GroundingJsonLdBuilderTest extends TestCase
{
    public function testWebPageNodeCarriesHeaderAndMainEntityReference(): void
    {
        $web = $this->builder()->buildWebPage($this->sampleDto())->toArray();

        $this->assertSame('WebPage', $web['@type']);
        $this->assertSame('#webpage', $web['@id']);
        $this->assertSame(['@id' => '#main'], $web['mainEntity']);
        $this->assertSame('CERN', $web['name']);
        $this->assertSame('de', $web['inLanguage']);
        $this->assertSame('2026-06-20', $web['dateModified']);
        $this->assertSame('Organization', $web['publisher']['@type']);
        $this->assertSame('Grounding Page Editorial', $web['maintainer']['name']);
    }

    public function testMainEntityTypeIdAndBackReference(): void
    {
        $main = $this->builder()->buildMainEntity($this->sampleDto())->toArray();

        $this->assertSame('Organization', $main['@type']);
        $this->assertSame('#main', $main['@id']);
        $this->assertSame(['@id' => '#webpage'], $main['mainEntityOfPage']);
        $this->assertSame('CERN', $main['name']);
    }

    public function testUrlIdentifiersAndSameAsAreMergedAndDeduplicated(): void
    {
        $main = $this->builder()->buildMainEntity($this->sampleDto())->toArray();

        $this->assertContains('https://www.wikidata.org/wiki/Q42944', $main['sameAs']);
        $this->assertContains('https://ror.org/01ggx4157', $main['sameAs']);
        $this->assertNotContains('25', $main['sameAs']);
    }

    public function testNonUrlDataBecomesAdditionalPropertyIncludingStatusAndVersion(): void
    {
        $main = $this->builder()->buildMainEntity($this->sampleDto())->toArray();
        $props = $main['additionalProperty'];

        $names = array_column($props, 'name', 'value');

        // fact-grid, timeline, non-URL identifier, status, version — alle als PropertyValue.
        $this->assertContains('PropertyValue', array_column($props, '@type'));
        $this->assertSame('Gegründet', $names['1954']);
        $this->assertSame('1989', $names['WWW-Vorschlag']);
        $this->assertSame('status', $names['Active Definition']);
        $this->assertSame('version', $names['1.6']);
        $this->assertContains('Mitgliedstaaten', array_column($props, 'propertyID'));
    }

    public function testDefinedTermsBecomeSeparateNodes(): void
    {
        $terms = $this->builder()->buildDefinedTerms($this->sampleDto());

        $this->assertCount(1, $terms);
        $this->assertSame('DefinedTerm', $terms[0]->toArray()['@type']);
        $this->assertSame('Offenes Wissen', $terms[0]->toArray()['name']);
    }

    public function testFaqBecomesFaqPageWithQuestions(): void
    {
        $faq = $this->builder()->buildFaq($this->sampleDto());

        $this->assertNotNull($faq);
        $array = $faq->toArray();
        $this->assertSame('FAQPage', $array['@type']);
        $this->assertSame('Was ist CERN?', $array['mainEntity'][0]['name']);
        $this->assertSame('Ein Forschungszentrum.', $array['mainEntity'][0]['acceptedAnswer']['text']);
    }

    public function testFaqIsNullWhenNoFaqRows(): void
    {
        $dto = new GroundingPageDto(name: 'X', schemaType: 'Organization');

        $this->assertNull($this->builder()->buildFaq($dto));
    }

    public function testCustomSchemaTypeProducesMatchingNodeType(): void
    {
        $dto = new GroundingPageDto(name: 'Klinik', schemaType: 'MedicalOrganization');

        $this->assertSame('MedicalOrganization', $this->builder()->buildMainEntity($dto)->toArray()['@type']);
    }

    public function testUnknownSchemaTypeFallsBackToThing(): void
    {
        $dto = new GroundingPageDto(name: 'X', schemaType: 'DefinitelyNotASchemaType');

        $this->assertSame('Thing', $this->builder()->buildMainEntity($dto)->toArray()['@type']);
    }

    public function testSourcesBecomeCitationCreativeWorks(): void
    {
        $web = $this->builder()->buildWebPage($this->sampleDto())->toArray();

        $this->assertSame(
            [['@type' => 'CreativeWork', 'name' => 'Website', 'url' => 'https://home.cern/']],
            $web['citation'],
        );
    }

    public function testCitationSkipsUnusableRowsAndFallsBackToUrlAsName(): void
    {
        $dto = new GroundingPageDto(name: 'X', schemaType: 'Thing', sections: [
            new GroundingSectionDto(sectionType: 'sources', sources: [
                ['title' => 'Nur Titel', 'url' => ''],
                ['title' => '', 'url' => 'https://nur-url/'],
                ['title' => '', 'url' => 'javascript:alert(1)'],
                ['title' => 'Böse', 'url' => 'javascript:alert(1)'],
            ]),
        ]);

        $this->assertSame(
            [
                ['@type' => 'CreativeWork', 'name' => 'Nur Titel'],
                ['@type' => 'CreativeWork', 'name' => 'https://nur-url/', 'url' => 'https://nur-url/'],
                ['@type' => 'CreativeWork', 'name' => 'Böse'],
            ],
            $this->builder()->buildWebPage($dto)->toArray()['citation'],
        );
    }

    public function testFurtherReadingBecomesDeduplicatedRelatedLinks(): void
    {
        $dto = new GroundingPageDto(name: 'X', schemaType: 'Thing', sections: [
            new GroundingSectionDto(sectionType: 'further-reading', furtherReading: [
                ['title' => 'A', 'url' => 'https://a/'],
                ['title' => 'Nur Titel', 'url' => ''],
                ['title' => 'Böse', 'url' => 'javascript:alert(1)'],
            ]),
            new GroundingSectionDto(sectionType: 'further-reading', furtherReading: [
                ['title' => 'A again', 'url' => 'https://a/'],
                ['title' => 'B', 'url' => 'http://b/'],
            ]),
        ]);

        $this->assertSame(['https://a/', 'http://b/'], $this->builder()->buildWebPage($dto)->toArray()['relatedLink']);
    }

    public function testNoCitationOrRelatedLinkWithoutRows(): void
    {
        $web = $this->builder()->buildWebPage(new GroundingPageDto(name: 'X', schemaType: 'Thing'))->toArray();

        $this->assertArrayNotHasKey('citation', $web);
        $this->assertArrayNotHasKey('relatedLink', $web);
    }

    public function testOrganizationGetsAreaServedAndParentOrganization(): void
    {
        $main = $this->builder()->buildMainEntity(new GroundingPageDto(
            name: 'CERN',
            schemaType: 'Organization',
            geographicScope: 'International',
            parentEntity: 'UNESCO',
            parentEntityUrl: 'https://www.unesco.org/',
        ))->toArray();

        $this->assertSame('International', $main['areaServed']);
        $this->assertSame('Organization', $main['parentOrganization']['@type']);
        $this->assertSame('UNESCO', $main['parentOrganization']['name']);
        $this->assertSame('https://www.unesco.org/', $main['parentOrganization']['url']);
        $this->assertArrayNotHasKey('additionalProperty', $main);
    }

    public function testCreativeWorkGetsSpatialCoverageAndIsPartOf(): void
    {
        $main = $this->builder()->buildMainEntity(new GroundingPageDto(
            name: 'Spec',
            schemaType: 'CreativeWork',
            geographicScope: 'Weltweit',
            parentEntity: 'Grounding Page Project',
        ))->toArray();

        $this->assertSame('Weltweit', $main['spatialCoverage']);
        $this->assertSame('CreativeWork', $main['isPartOf']['@type']);
        $this->assertSame('Grounding Page Project', $main['isPartOf']['name']);
        $this->assertArrayNotHasKey('url', $main['isPartOf']);
    }

    public function testOtherAndCustomTypesFallBackToPropertyValues(): void
    {
        foreach ([
            new GroundingPageDto(name: 'P', schemaType: 'Person', geographicScope: 'Hamburg', parentEntity: 'Firma', parentEntityUrl: 'https://firma/'),
            new GroundingPageDto(name: 'O', schemaType: 'Organization', geographicScope: 'Hamburg', parentEntity: 'Firma', parentEntityUrl: 'https://firma/', isCustomSchemaType: true),
        ] as $dto) {
            $main = $this->builder()->buildMainEntity($dto)->toArray();

            foreach (['areaServed', 'spatialCoverage', 'parentOrganization', 'isPartOf'] as $typed) {
                $this->assertArrayNotHasKey($typed, $main, $dto->schemaType);
            }

            $props = array_column($main['additionalProperty'], null, 'name');
            $this->assertSame('Hamburg', $props['geographicScope']['value']);
            $this->assertSame('Firma', $props['parentEntity']['value']);
            $this->assertSame('https://firma/', $props['parentEntity']['url']);
        }
    }

    public function testUnsafeParentUrlIsDropped(): void
    {
        $main = $this->builder()->buildMainEntity(new GroundingPageDto(
            name: 'CERN',
            schemaType: 'Organization',
            parentEntity: 'UNESCO',
            parentEntityUrl: 'javascript:alert(1)',
        ))->toArray();

        $this->assertArrayNotHasKey('url', $main['parentOrganization']);
    }

    public function testSegmentAndRelationshipsBecomePropertyValues(): void
    {
        $main = $this->builder()->buildMainEntity(new GroundingPageDto(
            name: 'CERN',
            schemaType: 'Organization',
            segment: 'Forschung',
            relationships: [
                ['relation' => 'Betreibt', 'name' => 'LHC', 'url' => 'https://home.cern/lhc'],
                ['relation' => 'Partner', 'name' => 'Y', 'url' => 'javascript:alert(1)'],
            ],
        ))->toArray();

        $props = array_column($main['additionalProperty'], null, 'name');

        $this->assertSame(['@type' => 'PropertyValue', 'name' => 'category', 'value' => 'Forschung'], $props['category']);
        $this->assertSame(
            ['@type' => 'PropertyValue', 'name' => 'Betreibt', 'value' => 'LHC', 'url' => 'https://home.cern/lhc'],
            $props['Betreibt'],
        );
        $this->assertSame(['@type' => 'PropertyValue', 'name' => 'Partner', 'value' => 'Y'], $props['Partner']);
    }

    public function testNoClassificationOutputWhenEmpty(): void
    {
        $main = $this->builder()->buildMainEntity(new GroundingPageDto(name: 'X', schemaType: 'Organization'))->toArray();

        foreach (['areaServed', 'parentOrganization', 'additionalProperty'] as $key) {
            $this->assertArrayNotHasKey($key, $main);
        }
    }

    private function builder(): GroundingJsonLdBuilder
    {
        return new GroundingJsonLdBuilder(new ResponseContextAccessor(new RequestStack()));
    }

    private function sampleDto(): GroundingPageDto
    {
        return new GroundingPageDto(
            name: 'CERN',
            schemaType: 'Organization',
            definition: 'Europäische Organisation für Kernforschung.',
            sameAs: ['https://www.wikidata.org/wiki/Q42944'],
            publisher: 'Grounding Page Project',
            maintainer: 'Grounding Page Editorial',
            status: 'Active Definition',
            entryVersion: '1.6',
            inLanguage: 'de',
            datePublished: '1954-09-29',
            dateModified: '2026-06-20',
            sections: [
                new GroundingSectionDto(sectionType: 'fact-grid', factGrid: [['label' => 'Gegründet', 'value' => '1954']]),
                new GroundingSectionDto(sectionType: 'timeline', timeline: [['year' => '1989', 'event' => 'WWW-Vorschlag']]),
                new GroundingSectionDto(sectionType: 'defined-terms', definedTerms: [['term' => 'Offenes Wissen', 'definition' => 'Frei zugänglich.']]),
                new GroundingSectionDto(sectionType: 'faq', faq: [['question' => 'Was ist CERN?', 'answer' => 'Ein Forschungszentrum.']]),
                new GroundingSectionDto(
                    sectionType: 'sources',
                    sources: [['title' => 'Website', 'url' => 'https://home.cern/']],
                    identifiers: [
                        ['label' => 'ROR', 'value' => 'https://ror.org/01ggx4157'],
                        ['label' => 'Mitgliedstaaten', 'value' => '25'],
                    ],
                ),
            ],
        );
    }
}
