<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Trassd\Contao\GroundingPages\Enum\GroundingSectionType;
use Trassd\Contao\GroundingPages\Enum\SectionRowSchema;

/*
 * Abschnitte einer Grounding Page (Kindtabelle, ptable = tl_grounding_page, manuell sortierbar).
 * Der Selektor sectionType schaltet per Subpalette (fieldName_fieldValue) das passende
 * rowWizard-Feld frei. rowWizard speichert je Zeile ein assoziatives Array nach Feldname.
 *
 * rowWizard-Felder und Subpaletten werden unten aus SectionRowSchema/GroundingSectionType
 * abgeleitet, damit das Spalten-Vokabular genau eine Wahrheit hat.
 */

/**
 * Baut die rowWizard-Feld-Definition aus dem Spalten-Owner. Alle Spalten sind Text;
 * das Label folgt der Konvention col_<columnKey>. Die &-Referenz hält die Übersetzung
 * lazy (TL_LANG ist beim DCA-Laden ggf. noch nicht befüllt).
 *
 * @return array<string, mixed>
 */
$groundingRowWizard = static function (SectionRowSchema $schema): array {
    $fields = [];

    foreach ($schema->columns() as $column) {
        $fields[$column] = [
            'label' => &$GLOBALS['TL_LANG']['tl_grounding_section']['col_'.$column],
            'inputType' => 'text',
        ];
    }

    return [
        'inputType' => 'rowWizard',
        'fields' => $fields,
        'eval' => ['tl_class' => 'clr'],
        'sql' => ['type' => 'blob', 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
    ];
};

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
    // Aus GroundingSectionType::fields() abgeleitet (siehe unten).
    'subpalettes' => [],
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
        // rowWizard-Felder (factGrid, timelineItems, …) werden unten aus SectionRowSchema abgeleitet.
        'published' => [
            'inputType' => 'checkbox',
            'toggle' => true,
            'default' => true,
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];

// Subpaletten: welcher Sektionstyp welche Felder freischaltet (SOURCES => 'sources,identifiers').
foreach (GroundingSectionType::cases() as $sectionType) {
    $GLOBALS['TL_DCA']['tl_grounding_section']['subpalettes']['sectionType_'.$sectionType->value]
        = implode(',', array_map(static fn (SectionRowSchema $field): string => $field->fieldName(), $sectionType->fields()));
}

// rowWizard-Felder: eine Definition pro Row-Schema, Spalten-Keys direkt aus dem Owner.
foreach (SectionRowSchema::cases() as $rowSchema) {
    $GLOBALS['TL_DCA']['tl_grounding_section']['fields'][$rowSchema->fieldName()] = $groundingRowWizard($rowSchema);
}
