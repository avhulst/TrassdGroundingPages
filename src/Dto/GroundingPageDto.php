<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Dto;

use Trassd\Contao\GroundingPages\Reference\SameAsPolicy;

final readonly class GroundingPageDto
{
    /**
     * @param list<string>                $sameAs
     * @param list<array<string, string>> $changelog
     * @param list<GroundingSectionDto>   $sections
     * @param list<array<string, string>> $relationships
     */
    public function __construct(
        public string $name,
        public string $schemaType,
        public string $entityType = '',
        public string $definition = '',
        public string $segment = '',
        public string $distinction = '',
        public array $sameAs = [],
        public string $publisher = '',
        public string $maintainer = '',
        public string $status = '',
        public string $entryVersion = '',
        public string $correctionContact = '',
        public string $inLanguage = '',
        public string|null $datePublished = null,
        public string|null $dateModified = null,
        public array $changelog = [],
        public array $sections = [],
        public string $geographicScope = '',
        public string $parentEntity = '',
        public string $parentEntityUrl = '',
        public array $relationships = [],
        public bool $isCustomSchemaType = false,
        public bool $showHumanNotice = true,
        public string $reviewerType = 'person',
        public string $reviewerName = '',
        public string|null $lastReviewed = null,
        public string $reviewStandard = '',
        public string $entityId = '',
        public string $anchorUrl = '',
    ) {
    }

    /**
     * Standard v1.7: Eine Seite gilt erst mit Reviewer und Review-Datum als geprüft.
     * Halbe Angaben (z. B. aus Importen) zählen als ungeprüft.
     */
    public function isReviewed(): bool
    {
        return '' !== $this->reviewerName && null !== $this->lastReviewed && '' !== $this->lastReviewed;
    }

    /**
     * RI-4: alle sameAs-Ziele, die sichtbar und im JSON-LD erscheinen. Erst das
     * Seitenfeld, dann URL-Kennungen aller Sektionen; dieselbe URL nur einmal.
     *
     * @return list<array{label: string, url: string}>
     */
    public function officialProfiles(): array
    {
        $out = [];

        foreach ($this->sameAs as $url) {
            // Nur http(s)-URLs: verhindert javascript:/data:-Links (Stored-XSS).
            if (self::isUrl($url)) {
                $out[$url] ??= ['label' => '', 'url' => $url];
            }
        }

        foreach ($this->identifierRows() as $row) {
            if (self::isUrl($row['value'])) {
                $out[$row['value']] ??= ['label' => $row['label'], 'url' => $row['value']];
            }
        }

        return array_values(array_filter($out, static fn (array $profile): bool => '' !== $profile['url']));
    }

    /**
     * @return list<string>
     */
    public function sameAsUrls(): array
    {
        return array_column($this->officialProfiles(), 'url');
    }

    /**
     * RI-E2: Kennungen ohne URL, als identifier (PropertyValue) und sichtbar.
     *
     * @return list<array{label: string, value: string}>
     */
    public function identifierValues(): array
    {
        $out = [];

        foreach ($this->identifierRows() as $row) {
            if ('' !== $row['label'] && '' !== $row['value'] && !self::isUrl($row['value'])) {
                $out[] = $row;
            }
        }

        return $out;
    }

    public function withHumanNotice(bool $show): self
    {
        return new self(...[...get_object_vars($this), 'showHumanNotice' => $show]);
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function identifierRows(): array
    {
        $rows = [];

        foreach ($this->sections as $section) {
            foreach ($section->identifiers as $row) {
                $rows[] = ['label' => $row['label'] ?? '', 'value' => $row['value'] ?? ''];
            }
        }

        return $rows;
    }

    private static function isUrl(string $value): bool
    {
        return SameAsPolicy::isAbsoluteHttpUrl($value);
    }
}
