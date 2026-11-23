<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Reference;

/*
 * Gründe, aus denen eine URL nach Standard 2.0 (RI-3) nicht in sameAs stehen darf.
 */
enum SameAsViolation: string
{
    case NotHttp = 'notHttp';
    case OwnDomain = 'ownDomain';
    case SearchOrApi = 'searchOrApi';
    case DoiOrArxiv = 'doiOrArxiv';
    case SectionAnchor = 'sectionAnchor';

    public function translationKey(): string
    {
        return 'tl_grounding_page.error.sameAs.'.$this->value;
    }
}
