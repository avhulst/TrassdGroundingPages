<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Trassd\Contao\GroundingPages\Enum\GroundingEntityType;

/*
 * Kopftabelle einer Grounding Page (die Entität + Governance). Die Abschnitte liegen in der
 * Kindtabelle tl_grounding_section (ptable). Ein Datensatz = eine Entität = ein JSON-LD-Graph.
 */
$GLOBALS['TL_DCA']['tl_grounding_page'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => ['tl_grounding_section'],
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'alias' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['title'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['title', 'entityType'],
            'format' => '%s <span class="tl_gray">[%s]</span>',
        ],
        'operations' => [
            'children' => [
                'href' => 'table=tl_grounding_section',
                'icon' => 'children.svg',
            ],
            'edit' => ['href' => 'act=edit', 'icon' => 'edit.svg'],
            'copy' => ['href' => 'act=copy', 'icon' => 'copy.svg'],
            'delete' => ['href' => 'act=delete', 'icon' => 'delete.svg'],
            'toggle' => [
                'href' => 'act=toggle&field=published',
                'icon' => 'visible.svg',
            ],
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
        ],
    ],
    'palettes' => [
        'default' => '{title_legend},title,alias,language,entityType,customSchemaType;{definition_legend},definition,segment,distinction,sameAs;{governance_legend},publisher,maintainer,status,entryVersion,datePublished,dateVerified,changelog,correctionContact;{publish_legend},published',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'title' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'alias' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alias', 'unique' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'language' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 5, 'tl_class' => 'w50'],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        'entityType' => [
            'inputType' => 'select',
            'options' => array_keys(GroundingEntityType::options()),
            'reference' => &$GLOBALS['TL_LANG']['tl_grounding_page']['entityType'],
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default 'segment'",
        ],
        'customSchemaType' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'definition' => [
            'inputType' => 'textarea',
            'eval' => ['rows' => 4, 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'segment' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'distinction' => [
            'inputType' => 'textarea',
            'eval' => ['rows' => 3, 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'sameAs' => [
            'inputType' => 'listWizard',
            'eval' => ['tl_class' => 'clr'],
            'sql' => 'blob NULL',
        ],
        'publisher' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'maintainer' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'status' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'entryVersion' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'datePublished' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => 'int(10) unsigned NULL',
        ],
        'dateVerified' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => 'int(10) unsigned NULL',
        ],
        'changelog' => [
            'inputType' => 'rowWizard',
            'fields' => [
                'date' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_page']['col_changeDate'], 'inputType' => 'text'],
                'change' => ['label' => &$GLOBALS['TL_LANG']['tl_grounding_page']['col_change'], 'inputType' => 'text'],
            ],
            'eval' => ['tl_class' => 'clr'],
            'sql' => ['type' => 'blob', 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
        ],
        'correctionContact' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'published' => [
            'inputType' => 'checkbox',
            'toggle' => true,
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
