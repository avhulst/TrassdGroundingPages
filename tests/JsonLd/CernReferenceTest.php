<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\JsonLd;

use Contao\CoreBundle\Routing\ResponseContext\JsonLd\JsonLdManager;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContext;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;
use Trassd\Contao\GroundingPages\Dto\GroundingSectionDto;
use Trassd\Contao\GroundingPages\JsonLd\GroundingJsonLdBuilder;

/**
 * Reproduziert den CERN-Referenzdatensatz (Vorlage Teil K) und prüft den
 * erzeugten Graphen.
 */
class CernReferenceTest extends TestCase
{
    public function testWebPageAndOrganizationMainEntity(): void
    {
        $nodes = $this->graph();

        $webPage = $this->firstOfType($nodes, 'WebPage');
        $main = $this->firstOfType($nodes, 'Organization', '#main');

        $this->assertSame('CERN', $webPage['name']);
        $this->assertSame('de', $webPage['inLanguage']);
        $this->assertSame('2026-06-20', $webPage['dateModified']);
        $this->assertSame('Grounding Page Project', $webPage['publisher']['name']);
        $this->assertSame('#main', $main['@id']);
        $this->assertSame('CERN', $main['name']);
    }

    public function testSameAsCarriesAllIdentityUrls(): void
    {
        $main = $this->firstOfType($this->graph(), 'Organization', '#main');

        foreach ([
            'https://www.wikidata.org/wiki/Q42944',
            'https://en.wikipedia.org/wiki/CERN',
            'https://ror.org/01ggx4157',
            'https://home.cern/',
        ] as $url) {
            $this->assertContains($url, $main['sameAs']);
        }
    }

    public function testAdditionalPropertyCarriesFactsStatusAndVersion(): void
    {
        $main = $this->firstOfType($this->graph(), 'Organization', '#main');
        $byName = array_column($main['additionalProperty'], 'value', 'name');

        $this->assertSame('29. September 1954', $byName['Gegründet']);
        $this->assertSame('Active Definition', $byName['status']);
        $this->assertSame('1.6', $byName['version']);
    }

    public function testDefinedTermAndFaqPageNodesExist(): void
    {
        $types = array_column($this->graph(), '@type');

        $this->assertContains('DefinedTerm', $types);
        $this->assertContains('FAQPage', $types);
    }

    private function cern(): GroundingPageDto
    {
        return new GroundingPageDto(
            name: 'CERN',
            schemaType: 'Organization',
            definition: 'CERN ist die Europäische Organisation für Kernforschung.',
            segment: 'Zwischenstaatliche wissenschaftliche Forschungsorganisation',
            distinction: 'Nicht zu verwechseln mit der ESA oder der ITER-Organisation.',
            sameAs: [
                'https://www.wikidata.org/wiki/Q42944',
                'https://en.wikipedia.org/wiki/CERN',
                'https://ror.org/01ggx4157',
                'https://home.cern/',
            ],
            publisher: 'Grounding Page Project',
            maintainer: 'Grounding Page Editorial',
            status: 'Active Definition',
            entryVersion: '1.6',
            inLanguage: 'de',
            datePublished: '1954-09-29',
            dateModified: '2026-06-20',
            sections: [
                new GroundingSectionDto(sectionType: 'fact-grid', sectionTitle: 'CERN: Kernfakten', factGrid: [
                    ['label' => 'Gegründet', 'value' => '29. September 1954'],
                    ['label' => 'Hauptsitz', 'value' => 'Meyrin, Großraum Genf, Schweiz'],
                ]),
                new GroundingSectionDto(sectionType: 'timeline', sectionTitle: 'CERN: Geschichte', timeline: [
                    ['year' => '1954', 'event' => 'Gründung'],
                    ['year' => '1989', 'event' => 'Tim Berners-Lee schlägt das WWW vor.'],
                ]),
                new GroundingSectionDto(sectionType: 'defined-terms', sectionTitle: 'CERN: Begriffe', definedTerms: [
                    ['term' => 'Offenes Wissen', 'definition' => 'Frei zugängliche Forschungsergebnisse.'],
                ]),
                new GroundingSectionDto(sectionType: 'faq', sectionTitle: 'CERN: Häufige Fragen', faq: [
                    ['question' => 'Was ist CERN?', 'answer' => 'Ein Teilchenphysik-Forschungszentrum.'],
                ]),
                new GroundingSectionDto(sectionType: 'sources', sectionTitle: 'CERN: Quellen & Identifikatoren',
                    sources: [['title' => 'Offizielle Website', 'url' => 'https://home.cern/']],
                    identifiers: [
                        ['label' => 'Wikidata', 'value' => 'https://www.wikidata.org/wiki/Q42944'],
                        ['label' => 'ROR', 'value' => 'https://ror.org/01ggx4157'],
                    ],
                ),
            ],
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function graph(): array
    {
        $context = new ResponseContext();
        $manager = new JsonLdManager($context);
        $context->add($manager);

        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $accessor = new ResponseContextAccessor($requestStack);
        $accessor->setResponseContext($context);

        (new GroundingJsonLdBuilder($accessor))->addToResponseContext($this->cern());

        return $manager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->toArray()['@graph'];
    }

    /**
     * @param list<array<string, mixed>> $nodes
     *
     * @return array<string, mixed>
     */
    private function firstOfType(array $nodes, string $type, string|null $id = null): array
    {
        foreach ($nodes as $node) {
            if ($node['@type'] === $type && (null === $id || ($node['@id'] ?? null) === $id)) {
                return $node;
            }
        }

        $this->fail(\sprintf('No node of type %s%s found.', $type, null !== $id ? ' with @id '.$id : ''));
    }
}
