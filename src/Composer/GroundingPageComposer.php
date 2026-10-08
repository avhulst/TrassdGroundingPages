<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Composer;

use Contao\GroundingPageModel;
use Contao\GroundingSectionModel;
use Contao\StringUtil;
use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;
use Trassd\Contao\GroundingPages\Dto\GroundingSectionDto;
use Trassd\Contao\GroundingPages\Enum\GroundingEntityType;
use Trassd\Contao\GroundingPages\Enum\GroundingSectionType;
use Trassd\Contao\GroundingPages\Enum\SectionRowSchema;

final class GroundingPageComposer
{
    public function compose(GroundingPageModel $page): GroundingPageDto
    {
        $sections = [];

        foreach ($page->getPublishedSections() ?? [] as $section) {
            $sections[] = $this->composeSection($section);
        }

        return new GroundingPageDto(
            name: (string) $page->title,
            schemaType: $this->resolveSchemaType($page),
            entityType: (string) $page->entityType,
            definition: (string) $page->definition,
            segment: (string) $page->segment,
            distinction: (string) $page->distinction,
            sameAs: $this->stringList($page->sameAs),
            publisher: (string) $page->publisher,
            maintainer: (string) $page->maintainer,
            status: (string) $page->status,
            entryVersion: (string) $page->entryVersion,
            correctionContact: trim((string) $page->correctionContact),
            inLanguage: (string) $page->language,
            datePublished: $this->formatDate($page->datePublished),
            dateModified: $this->dateModified($page),
            changelog: $this->rowsOf($page->changelog, 'date', 'change'),
            sections: $sections,
            geographicScope: trim((string) $page->geographicScope),
            parentEntity: trim((string) $page->parentEntity),
            parentEntityUrl: trim((string) $page->parentEntityUrl),
            relationships: $this->relationships($page->relationships),
            isCustomSchemaType: '' !== trim((string) $page->customSchemaType),
        );
    }

    private function composeSection(GroundingSectionModel $section): GroundingSectionDto
    {
        $type = GroundingSectionType::fromStringOrFallback((string) $section->sectionType);

        return new GroundingSectionDto(
            sectionType: (string) $section->sectionType,
            sectionTitle: (string) $section->sectionTitle,
            factGrid: $this->rows($section, $type, SectionRowSchema::FactGrid),
            timeline: $this->rows($section, $type, SectionRowSchema::Timeline),
            definedTerms: $this->rows($section, $type, SectionRowSchema::DefinedTerms),
            faq: $this->rows($section, $type, SectionRowSchema::Faq),
            sources: $this->rows($section, $type, SectionRowSchema::Sources),
            identifiers: $this->rows($section, $type, SectionRowSchema::Identifiers),
            furtherReading: $this->rows($section, $type, SectionRowSchema::FurtherReading),
        );
    }

    /**
     * Liest ein rowWizard-Feld, sofern der Sektionstyp es freischaltet. Die
     * Spalten-Keys stammen aus dem Owner (SectionRowSchema), nicht aus Literalen.
     *
     * @return list<array<string, string>>
     */
    private function rows(GroundingSectionModel $section, GroundingSectionType $type, SectionRowSchema $schema): array
    {
        if (!\in_array($schema, $type->fields(), true)) {
            return [];
        }

        return $this->rowsOf($section->{$schema->fieldName()}, ...$schema->columns());
    }

    private function resolveSchemaType(GroundingPageModel $page): string
    {
        $custom = trim((string) $page->customSchemaType);

        if ('' !== $custom) {
            return $custom;
        }

        return GroundingEntityType::fromStringOrFallback((string) $page->entityType)->schemaType();
    }

    private function dateModified(GroundingPageModel $page): string|null
    {
        foreach ([$page->dateVerified, $page->tstamp, $page->datePublished] as $timestamp) {
            if ((int) $timestamp > 0) {
                return date('Y-m-d', (int) $timestamp);
            }
        }

        return null;
    }

    private function formatDate(mixed $timestamp): string|null
    {
        return (int) $timestamp > 0 ? date('Y-m-d', (int) $timestamp) : null;
    }

    /**
     * Liest ein rowWizard-Feld: trimmt alle Spalten und verwirft Zeilen, deren Werte
     * sämtlich leer sind.
     *
     * @return list<array<string, string>>
     */
    private function rowsOf(mixed $raw, string ...$keys): array
    {
        $out = [];

        foreach (StringUtil::deserialize($raw, true) as $row) {
            if (!\is_array($row)) {
                continue;
            }

            $values = [];

            foreach ($keys as $key) {
                $values[$key] = trim((string) ($row[$key] ?? ''));
            }

            if ('' === implode('', $values)) {
                continue;
            }

            $out[] = $values;
        }

        return $out;
    }

    /**
     * Beziehungen brauchen Beziehung und Name; die URL ist optional.
     *
     * @return list<array<string, string>>
     */
    private function relationships(mixed $raw): array
    {
        return array_values(array_filter(
            $this->rowsOf($raw, 'relation', 'name', 'url'),
            static fn (array $row): bool => '' !== $row['relation'] && '' !== $row['name'],
        ));
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $raw): array
    {
        $out = [];

        foreach (StringUtil::deserialize($raw, true) as $row) {
            $value = \is_array($row) ? trim((string) reset($row)) : trim((string) $row);

            if ('' !== $value) {
                $out[] = $value;
            }
        }

        return array_values(array_unique($out));
    }
}
