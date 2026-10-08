<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Dto;

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
    ) {
    }
}
