<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\JsonLd;

use Contao\CoreBundle\Routing\ResponseContext\JsonLd\JsonLdManager;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Graph;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\WebPage;
use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;
use Trassd\Contao\GroundingPages\Dto\GroundingSectionDto;
use Trassd\Contao\GroundingPages\Enum\SectionRowSchema;

final readonly class GroundingJsonLdBuilder
{
    public function __construct(private ResponseContextAccessor $responseContextAccessor)
    {
    }

    public function addToResponseContext(GroundingPageDto $dto): void
    {
        $context = $this->responseContextAccessor->getResponseContext();

        if (null === $context || !$context->has(JsonLdManager::class)) {
            return;
        }

        // set() statt add(): der Core seedet eine (leere) WebPage am
        // Standard-Identifier; wir überschreiben sie, sodass es genau eine WebPage pro
        // Seite gibt. collectFinalScriptFromGraphs() ruft der Core
        // (ContentCompositionBuilder) selbst auf.
        $this->populate($context->get(JsonLdManager::class)->getGraphForSchema(JsonLdManager::SCHEMA_ORG), $dto);
    }

    public function buildWebPage(GroundingPageDto $dto): WebPage
    {
        $webPage = Schema::webPage();
        $webPage->setProperty('@id', '#webpage');
        $webPage->setProperty('mainEntity', ['@id' => '#main']);
        $webPage->name($dto->name);
        $webPage->description($dto->definition);
        $webPage->inLanguage($dto->inLanguage);
        // dateCreated/dateModified sind in spatie auf DateTimeInterface typisiert; wir
        // führen JSON-LD-Datumsstrings ('Y-m-d') direkt über setProperty ein.
        $webPage->setProperty('dateCreated', $dto->datePublished);
        $webPage->setProperty('dateModified', $dto->dateModified);

        if ('' !== $dto->publisher) {
            $webPage->publisher(Schema::organization()->name($dto->publisher));
        }

        if ('' !== $dto->maintainer) {
            $webPage->setProperty('maintainer', Schema::organization()->name($dto->maintainer));
        }

        $citations = $this->buildCitations($dto);

        if ([] !== $citations) {
            $webPage->setProperty('citation', $citations);
        }

        $relatedLinks = $this->buildRelatedLinks($dto);

        if ([] !== $relatedLinks) {
            $webPage->setProperty('relatedLink', $relatedLinks);
        }

        return $webPage;
    }

    public function buildMainEntity(GroundingPageDto $dto): BaseType
    {
        $main = $this->createMainType('' !== $dto->schemaType ? $dto->schemaType : 'Thing');
        $main->setProperty('@id', '#main');
        $main->setProperty('mainEntityOfPage', ['@id' => '#webpage']);
        $main->setProperty('name', $dto->name);
        $main->setProperty('description', $dto->definition);

        $sameAs = $this->collectSameAs($dto);

        if ([] !== $sameAs) {
            $main->setProperty('sameAs', $sameAs);
        }

        $additional = $this->buildAdditionalProperties($dto);

        if ([] !== $additional) {
            $main->setProperty('additionalProperty', $additional);
        }

        return $main;
    }

    /**
     * @return list<BaseType>
     */
    public function buildDefinedTerms(GroundingPageDto $dto): array
    {
        $nodes = [];
        [$termKey, $definitionKey] = SectionRowSchema::DefinedTerms->columns();

        foreach ($this->collect($dto, static fn (GroundingSectionDto $section): array => $section->definedTerms) as $term) {
            if ('' === ($term[$termKey] ?? '')) {
                continue;
            }

            $node = Schema::definedTerm()->name($term[$termKey]);
            $node->description($term[$definitionKey] ?? '');
            $nodes[] = $node;
        }

        return $nodes;
    }

    public function buildFaq(GroundingPageDto $dto): BaseType|null
    {
        $questions = [];
        [$questionKey, $answerKey] = SectionRowSchema::Faq->columns();

        foreach ($this->collect($dto, static fn (GroundingSectionDto $section): array => $section->faq) as $entry) {
            if ('' === ($entry[$questionKey] ?? '') || '' === ($entry[$answerKey] ?? '')) {
                continue;
            }

            $questions[] = Schema::question()
                ->name($entry[$questionKey])
                ->acceptedAnswer(Schema::answer()->text($entry[$answerKey]))
            ;
        }

        if ([] === $questions) {
            return null;
        }

        return Schema::fAQPage()->mainEntity($questions);
    }

    private function populate(Graph $graph, GroundingPageDto $dto): void
    {
        $graph->set($this->buildWebPage($dto));
        $graph->set($this->buildMainEntity($dto), 'grounding-main');

        foreach ($this->buildDefinedTerms($dto) as $index => $term) {
            $graph->set($term, 'grounding-term-'.$index);
        }

        $faqPage = $this->buildFaq($dto);

        if (null !== $faqPage) {
            $graph->set($faqPage, 'grounding-faq');
        }
    }

    private function createMainType(string $schemaType): BaseType
    {
        return match ($schemaType) {
            'Organization' => Schema::organization(),
            'Person' => Schema::person(),
            'Product' => Schema::product(),
            'Service' => Schema::service(),
            'SoftwareApplication' => Schema::softwareApplication(),
            'DefinedTerm' => Schema::definedTerm(),
            'CreativeWork' => Schema::creativeWork(),
            'Dataset' => Schema::dataset(),
            'HowTo' => Schema::howTo(),
            'Place' => Schema::place(),
            'Event' => Schema::event(),
            'Project' => Schema::project(),
            'Thing' => Schema::thing(),
            default => $this->createCustomType($schemaType),
        };
    }

    private function createCustomType(string $schemaType): BaseType
    {
        $factory = lcfirst($schemaType);

        if (method_exists(Schema::class, $factory)) {
            $node = Schema::$factory();

            if ($node instanceof BaseType) {
                return $node;
            }
        }

        return Schema::thing();
    }

    /**
     * @return list<string>
     */
    private function collectSameAs(GroundingPageDto $dto): array
    {
        $sameAs = $dto->sameAs;
        [, $valueKey] = SectionRowSchema::Identifiers->columns();

        foreach ($this->collect($dto, static fn (GroundingSectionDto $section): array => $section->identifiers) as $identifier) {
            $value = $identifier[$valueKey] ?? '';

            if ($this->isUrl($value)) {
                $sameAs[] = $value;
            }
        }

        return array_values(array_unique(array_filter($sameAs)));
    }

    /**
     * @return list<array<string, string>>
     */
    private function buildAdditionalProperties(GroundingPageDto $dto): array
    {
        $props = [];
        [$factLabelKey, $factValueKey] = SectionRowSchema::FactGrid->columns();
        [$identifierLabelKey, $identifierValueKey] = SectionRowSchema::Identifiers->columns();
        [$yearKey, $eventKey] = SectionRowSchema::Timeline->columns();

        foreach ($this->collect($dto, static fn (GroundingSectionDto $section): array => $section->factGrid) as $fact) {
            if ('' !== ($fact[$factLabelKey] ?? '') && '' !== ($fact[$factValueKey] ?? '')) {
                $props[] = ['@type' => 'PropertyValue', 'name' => $fact[$factLabelKey], 'value' => $fact[$factValueKey]];
            }
        }

        foreach ($this->collect($dto, static fn (GroundingSectionDto $section): array => $section->identifiers) as $identifier) {
            $label = $identifier[$identifierLabelKey] ?? '';
            $value = $identifier[$identifierValueKey] ?? '';

            if ('' !== $label && '' !== $value && !$this->isUrl($value)) {
                $props[] = ['@type' => 'PropertyValue', 'propertyID' => $label, 'name' => $label, 'value' => $value];
            }
        }

        foreach ($this->collect($dto, static fn (GroundingSectionDto $section): array => $section->timeline) as $item) {
            if ('' !== ($item[$yearKey] ?? '') && '' !== ($item[$eventKey] ?? '')) {
                $props[] = ['@type' => 'PropertyValue', 'name' => $item[$yearKey], 'value' => $item[$eventKey]];
            }
        }

        if ('' !== $dto->status) {
            $props[] = ['@type' => 'PropertyValue', 'name' => 'status', 'value' => $dto->status];
        }

        if ('' !== $dto->entryVersion) {
            $props[] = ['@type' => 'PropertyValue', 'name' => 'version', 'value' => $dto->entryVersion];
        }

        return $props;
    }

    /**
     * Quellen → citation. Ohne Titel dient die URL als Name; nicht-http(s)-URLs
     * werden nie als url ausgegeben.
     *
     * @return list<BaseType>
     */
    private function buildCitations(GroundingPageDto $dto): array
    {
        $citations = [];
        [$titleKey, $urlKey] = SectionRowSchema::Sources->columns();

        foreach ($this->collect($dto, static fn (GroundingSectionDto $section): array => $section->sources) as $source) {
            $title = $source[$titleKey] ?? '';
            $url = $source[$urlKey] ?? '';
            $hasUrl = $this->isUrl($url);

            if ('' === $title && !$hasUrl) {
                continue;
            }

            $work = Schema::creativeWork()->name('' !== $title ? $title : $url);

            if ($hasUrl) {
                $work->url($url);
            }

            $citations[] = $work;
        }

        return $citations;
    }

    /**
     * Further Reading → relatedLink (schema.org erwartet URL-Strings).
     *
     * @return list<string>
     */
    private function buildRelatedLinks(GroundingPageDto $dto): array
    {
        $links = [];
        [, $urlKey] = SectionRowSchema::FurtherReading->columns();

        foreach ($this->collect($dto, static fn (GroundingSectionDto $section): array => $section->furtherReading) as $row) {
            $url = $row[$urlKey] ?? '';

            if ($this->isUrl($url)) {
                $links[] = $url;
            }
        }

        return array_values(array_unique($links));
    }

    /**
     * @param callable(GroundingSectionDto): list<array<string, string>> $extract
     *
     * @return list<array<string, string>>
     */
    private function collect(GroundingPageDto $dto, callable $extract): array
    {
        $out = [];

        foreach ($dto->sections as $section) {
            foreach ($extract($section) as $row) {
                $out[] = $row;
            }
        }

        return $out;
    }

    private function isUrl(string $value): bool
    {
        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://');
    }
}
