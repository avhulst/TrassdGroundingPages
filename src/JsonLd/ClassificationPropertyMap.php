<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\JsonLd;

/*
 * Wählt für die Klassifikationsangaben (v1.6.1) die typgerechte schema.org-Property.
 * null bedeutet: Der Typ kennt keine passende Property, Ausgabe als PropertyValue in
 * additionalProperty. Eigene Typen (customSchemaType) fallen immer zurück, weil ihre
 * Hierarchie unbekannt ist.
 */
final class ClassificationPropertyMap
{
    private const array GEOGRAPHIC_SCOPE = [
        'Organization' => 'areaServed',
        'Service' => 'areaServed',
        'CreativeWork' => 'spatialCoverage',
        'Dataset' => 'spatialCoverage',
    ];

    private const array PARENT_ENTITY = [
        'Organization' => 'parentOrganization',
        'CreativeWork' => 'isPartOf',
        'Dataset' => 'isPartOf',
    ];

    /**
     * @return array{geographicScope: string|null, parentEntity: string|null}
     */
    public static function resolve(string $schemaType, bool $isCustomType): array
    {
        if ($isCustomType) {
            return ['geographicScope' => null, 'parentEntity' => null];
        }

        return [
            'geographicScope' => self::GEOGRAPHIC_SCOPE[$schemaType] ?? null,
            'parentEntity' => self::PARENT_ENTITY[$schemaType] ?? null,
        ];
    }
}
