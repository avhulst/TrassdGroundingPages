<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\JsonLd;

use Contao\CoreBundle\Routing\ResponseContext\JsonLd\JsonLdManager;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContext;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use PHPUnit\Framework\TestCase;
use Spatie\SchemaOrg\Schema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;
use Trassd\Contao\GroundingPages\Dto\GroundingSectionDto;
use Trassd\Contao\GroundingPages\JsonLd\GroundingJsonLdBuilder;

class GroundingJsonLdSchemaValidationTest extends TestCase
{
    public function testProducesExactlyOneGraphWithSchemaOrgContext(): void
    {
        [, $manager, $builder] = $this->bootContext();

        $builder->addToResponseContext($this->dto());
        $graph = $manager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->toArray();

        $this->assertSame('https://schema.org', $graph['@context']);
        $this->assertIsArray($graph['@graph']);
    }

    public function testGraphHasExactlyOneWebPageAndOneMainEntityWithConsistentCrossReferences(): void
    {
        [, $manager, $builder] = $this->bootContext();

        $builder->addToResponseContext($this->dto());
        $nodes = $manager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->toArray()['@graph'];

        $webPages = array_values(array_filter($nodes, static fn (array $n): bool => 'WebPage' === $n['@type']));
        $mains = array_values(array_filter($nodes, static fn (array $n): bool => '#main' === ($n['@id'] ?? null)));

        $this->assertCount(1, $webPages);
        $this->assertCount(1, $mains);
        $this->assertSame('Organization', $mains[0]['@type']);
        $this->assertSame(['@id' => '#main'], $webPages[0]['mainEntity']);
        $this->assertSame(['@id' => '#webpage'], $mains[0]['mainEntityOfPage']);
    }

    public function testGraphContainsDefinedTermAndFaqPageNodes(): void
    {
        [, $manager, $builder] = $this->bootContext();

        $builder->addToResponseContext($this->dto());
        $types = array_column($manager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->toArray()['@graph'], '@type');

        $this->assertContains('DefinedTerm', $types);
        $this->assertContains('FAQPage', $types);
    }

    public function testOverwritesTheCoreSeededEmptyWebPageWithoutDuplicating(): void
    {
        [, $manager, $builder] = $this->bootContext();

        // Core seedet eine leere WebPage am Standard-Identifier.
        $manager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->set(Schema::webPage());

        $builder->addToResponseContext($this->dto());
        $nodes = $manager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->toArray()['@graph'];

        $webPages = array_filter($nodes, static fn (array $n): bool => 'WebPage' === $n['@type']);

        $this->assertCount(1, $webPages);
    }

    public function testNoResponseContextIsANoOp(): void
    {
        $builder = new GroundingJsonLdBuilder(new ResponseContextAccessor(new RequestStack()));

        $builder->addToResponseContext($this->dto());

        $this->expectNotToPerformAssertions();
    }

    public function testContextWithoutJsonLdManagerIsANoOp(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $accessor = new ResponseContextAccessor($requestStack);
        $accessor->setResponseContext(new ResponseContext());

        (new GroundingJsonLdBuilder($accessor))->addToResponseContext($this->dto());

        $this->expectNotToPerformAssertions();
    }

    public function testClassificationOnPersonUsesOnlyAdditionalProperty(): void
    {
        [, $manager, $builder] = $this->bootContext();

        $builder->addToResponseContext(new GroundingPageDto(
            name: 'Ada',
            schemaType: 'Person',
            geographicScope: 'London',
            parentEntity: 'Analytical Society',
        ));

        $nodes = $manager->getGraphForSchema(JsonLdManager::SCHEMA_ORG)->toArray()['@graph'];
        $mains = array_values(array_filter($nodes, static fn (array $n): bool => '#main' === ($n['@id'] ?? null)));

        $this->assertCount(1, $mains);

        foreach (['areaServed', 'spatialCoverage', 'parentOrganization', 'isPartOf'] as $typed) {
            $this->assertArrayNotHasKey($typed, $mains[0]);
        }

        $this->assertContains('geographicScope', array_column($mains[0]['additionalProperty'], 'name'));
    }

    private function dto(): GroundingPageDto
    {
        return new GroundingPageDto(
            name: 'CERN',
            schemaType: 'Organization',
            definition: 'Forschungszentrum.',
            sections: [
                new GroundingSectionDto(sectionType: 'defined-terms', definedTerms: [['term' => 'Begriff', 'definition' => 'Def.']]),
                new GroundingSectionDto(sectionType: 'faq', faq: [['question' => 'Was?', 'answer' => 'Das.']]),
            ],
        );
    }

    /**
     * @return array{ResponseContext, JsonLdManager, GroundingJsonLdBuilder}
     */
    private function bootContext(): array
    {
        $context = new ResponseContext();
        $manager = new JsonLdManager($context);
        $context->add($manager);

        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $accessor = new ResponseContextAccessor($requestStack);
        $accessor->setResponseContext($context);

        return [$context, $manager, new GroundingJsonLdBuilder($accessor)];
    }
}
