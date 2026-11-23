<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Reference;

/*
 * RI-1/RI-2: Seiten-URL, Entity-@id und Referenzanker einer gerenderten Grounding Page.
 */
final readonly class EntityIdentity
{
    public function __construct(
        public string $pageUrl,
        public string $entityId,
        public string $anchorUrl,
        public bool $isAnchor,
    ) {
    }
}
