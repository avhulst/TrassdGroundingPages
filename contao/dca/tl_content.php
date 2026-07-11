<?php

declare(strict_types=1);

/*
 * Content-Element „Grounding Page": platziert eine komplette Grounding Page (aus tl_grounding_page)
 * in einem beliebigen Artikel. Das Auswahlfeld groundingPage referenziert den Datensatz.
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['grounding_page'] = '{type_legend},type,headline;{grounding_legend},groundingPage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['groundingPage'] = [
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_grounding_page.title',
    'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => 'int(10) unsigned NOT NULL default 0',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];
