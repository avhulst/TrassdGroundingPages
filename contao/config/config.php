<?php

declare(strict_types=1);

use Contao\GroundingPageModel;
use Contao\GroundingSectionModel;

/*
 * Ein Backend-Modul mit Kopf- und Kindtabelle (Parent-Child): tl_grounding_section hängt per
 * ptable an tl_grounding_page und ist so als Kind-Liste navigierbar (ein Datensatz = eine Entität).
 */
$GLOBALS['BE_MOD']['content']['grounding_pages'] = [
    'tables' => ['tl_grounding_page', 'tl_grounding_section'],
];

$GLOBALS['TL_MODELS']['tl_grounding_page'] = GroundingPageModel::class;
$GLOBALS['TL_MODELS']['tl_grounding_section'] = GroundingSectionModel::class;

// Frontend-Styling (nach contao:symlinks unter public/bundles/trassdgroundingpages/ verfügbar).
$GLOBALS['TL_CSS']['grounding_pages'] = 'bundles/trassdgroundingpages/grounding.css|static';
