<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Reference;

use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;

/*
 * Leitet die Identität ab: explizites entityId gewinnt; sonst Anker-URL (Feld
 * anchorUrl, ersatzweise die Seiten-URL) plus Typ-Fragment. Der Automatismus ist
 * nur stabil, solange der Datensatz auf genau einer Seite in einer Sprache steht.
 */
final class EntityIdentityResolver
{
    public function resolve(GroundingPageDto $dto, string $pageUrl): EntityIdentity
    {
        $pageUrl = self::stripQueryAndFragment($pageUrl);
        $anchorUrl = '' !== $dto->anchorUrl ? $dto->anchorUrl : $pageUrl;
        $entityId = '' !== $dto->entityId
            ? $dto->entityId
            : self::stripQueryAndFragment($anchorUrl).self::fragmentFor($dto->schemaType);

        return new EntityIdentity(
            pageUrl: $pageUrl,
            entityId: $entityId,
            anchorUrl: $anchorUrl,
            isAnchor: self::stripQueryAndFragment($anchorUrl) === $pageUrl,
        );
    }

    public static function fragmentFor(string $schemaType): string
    {
        $type = strtolower(trim($schemaType));

        return '#'.('' === $type || 'thing' === $type ? 'entity' : preg_replace('/[^a-z0-9]/', '', $type));
    }

    public static function stripQueryAndFragment(string $url): string
    {
        return (string) preg_replace('/[?#].*$/', '', $url);
    }
}
