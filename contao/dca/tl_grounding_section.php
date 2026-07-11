<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Trassd\Contao\GroundingPages\Enum\GroundingSectionType;

/*
 * Abschnitte einer Grounding Page (Kindtabelle, ptable = tl_grounding_page, manuell sortierbar).
 * Der Selektor sectionType schaltet per Subpalette (fieldName_fieldValue) das passende
 * rowWizard-Feld frei. rowWizard speichert je Zeile ein assoziatives Array nach Feldname.
 */
$GLOBALS['TL_DCA']['tl_grounding_section'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_grounding_page',
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['sorting'],
            'headerFields' => ['title', 'entityType'],
            'panelLayout' => 'limit',
        ],
        'label' => [
            'fields' => ['sectionType', 'sectionTitle'],
            'format' => '%s <span class="tl_gray">%s</span>',
        ],
        'operations' => [
            'edit' => ['href' => 'act=edit', 'icon' => 'edit.svg'],
            'copy' => ['href' => 'act=copy', 'icon' => 'copy.svg'],
            'cut' => ['href' => 'act=paste&mode=cut', 'icon' => 'cut.svg'],
            'delete' => ['href' => 'act=delete', 'icon' => 'delete.svg'],
            'toggle' => [
                'href' => 'act=toggle&field=published',
                'icon' => 'visible.svg',
            ],
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
        ],
    ],
    'palettes' => [
        '__selector__' => ['sectionType'],
        'default' => '{section_legend},sectionType,sectionTitle;{publish_legend},published',
    ],
    'subpalettes' => [
        'sectionType_fact-grid' => 'factGrid',
        'sectionType_timeline' => 'timelineItems',
        'sectionType_defined-terms' => 'definedTerms',
        'sectionType_faq' => 'faqItems',
        'sectionType_sources' => 'sources,identifiers',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'sorting' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'sectionType' => [
            'inputType' => 'select',
            'options' => GroundingSectionType::values(),
            'reference' => &$GLOBALS['TL_LANG']['tl_grounding_section']['sectionType'],
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default 'fact-grid'",
        ],
        'sectionTitle' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'factGrid' => [
            'inputType' => 'rowWizard',
            'fields' => [
                'label' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_section']['col_label'], 'inputType' => 'text'],
                'value' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_section']['col_value'], 'inputType' => 'text'],
            ],
            'eval' => ['tl_class' => 'clr'],
            'sql' => ['type' => 'blob', 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
        ],
        'timelineItems' => [
            'inputType' => 'rowWizard',
            'fields' => [
                'year' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_section']['col_year'], 'inputType' => 'text'],
                'event' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_section']['col_event'], 'inputType' => 'text'],
            ],
            'eval' => ['tl_class' => 'clr'],
            'sql' => ['type' => 'blob', 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
        ],
        'definedTerms' => [
            'inputType' => 'rowWizard',
            'fields' => [
                'term' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_section']['col_term'], 'inputType' => 'text'],
                'definition' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_section']['col_definition'], 'inputType' => 'text'],
            ],
            'eval' => ['tl_class' => 'clr'],
            'sql' => ['type' => 'blob', 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
        ],
        'faqItems' => [
            'inputType' => 'rowWizard',
            'fields' => [
                'question' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_section']['col_question'], 'inputType' => 'text'],
                'answer' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_section']['col_answer'], 'inputType' => 'text'],
            ],
            'eval' => ['tl_class' => 'clr'],
            'sql' => ['type' => 'blob', 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
        ],
        'sources' => [
            'inputType' => 'rowWizard',
            'fields' => [
                'title' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_section']['col_title'], 'inputType' => 'text'],
                'url' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_section']['col_url'], 'inputType' => 'text'],
            ],
            'eval' => ['tl_class' => 'clr'],
            'sql' => ['type' => 'blob', 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
        ],
        'identifiers' => [
            'inputType' => 'rowWizard',
            'fields' => [
                'label' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_section']['col_label'], 'inputType' => 'text'],
                'value' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_section']['col_value'], 'inputType' => 'text'],
            ],
            'eval' => ['tl_class' => 'clr'],
            'sql' => ['type' => 'blob', 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
        ],
        'published' => [
            'inputType' => 'checkbox',
            'toggle' => true,
            'default' => true,
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
