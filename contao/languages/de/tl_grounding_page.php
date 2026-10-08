<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['tl_grounding_page']['title_legend'] = 'Entität';
$GLOBALS['TL_LANG']['tl_grounding_page']['definition_legend'] = 'Definition & Abgrenzung';
$GLOBALS['TL_LANG']['tl_grounding_page']['governance_legend'] = 'Governance';
$GLOBALS['TL_LANG']['tl_grounding_page']['publish_legend'] = 'Veröffentlichung';

$GLOBALS['TL_LANG']['tl_grounding_page']['title'] = ['Name / Titel', 'Sichtbarer Name der Entität und interner Titel.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['alias'] = ['Alias', 'URL-freundlicher, eindeutiger Bezeichner.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['language'] = ['Sprache', 'Sprachcode dieses Datensatzes (z. B. „de", „en") — fließt als inLanguage ins JSON-LD.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType'] = ['Entitätstyp', 'schema.org-Klasse der Entität (Fallback: Thing).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['customSchemaType'] = ['Eigener schema.org-Typ', 'Überschreibt die Zuordnung des Entitätstyps (optional, für beliebige @type).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['definition'] = ['Kanonische Definition', 'Kurze, faktische Definition der Entität (nicht werblich).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['segment'] = ['Segment', 'Segment- bzw. Kategoriezuordnung der Entität.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['geographicScope'] = ['Geografischer Bereich', 'Räumlicher Wirkungs- oder Geltungsbereich (z. B. „International", „DACH", „Hamburg").'];
$GLOBALS['TL_LANG']['tl_grounding_page']['parentEntity'] = ['Übergeordnete Entität', 'Name der übergeordneten Organisation oder Entität (optional).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['parentEntityUrl'] = ['URL der übergeordneten Entität', 'Offizielle URL oder Grounding Page der übergeordneten Entität (optional).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['relationships'] = ['Wichtige Beziehungen', 'Zeilen aus Beziehung, Name und optionaler URL (z. B. „Mitglied von · EIROforum").'];
$GLOBALS['TL_LANG']['tl_grounding_page']['distinction'] = ['Abgrenzung', 'Womit die Entität nicht verwechselt werden darf.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['sameAs'] = ['sameAs-Verweise', 'Externe Identitäts-URLs (Wikidata, Wikipedia, ROR, offizielle Website).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['publisher'] = ['Herausgeber', 'Für die Definition verantwortliche Organisation.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['maintainer'] = ['Verantwortlich', 'Pflegende/verantwortliche Stelle des Eintrags.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['status'] = ['Status', 'Redaktioneller Status (z. B. „Active Definition", „Draft").'];
$GLOBALS['TL_LANG']['tl_grounding_page']['entryVersion'] = ['Version', 'Version des Eintrags (z. B. „1.0").'];
$GLOBALS['TL_LANG']['tl_grounding_page']['datePublished'] = ['Veröffentlicht am', 'Erstveröffentlichungsdatum.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['dateVerified'] = ['Zuletzt geprüft', 'Datum der letzten Prüfung — wird als dateModified ausgegeben.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['published'] = ['Veröffentlichen', 'Die Grounding Page im Frontend sichtbar machen.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['changelog'] = ['Änderungsprotokoll', 'Zeilen aus Datum und Änderung (Governance, sichtbar im Footer).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['correctionContact'] = ['Korrektur-/Kontaktpfad', 'E-Mail oder URL für Korrekturhinweise (sichtbar im Footer).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['col_changeDate'] = 'Datum';
$GLOBALS['TL_LANG']['tl_grounding_page']['col_change'] = 'Änderung';
$GLOBALS['TL_LANG']['tl_grounding_page']['col_relation'] = 'Beziehung';
$GLOBALS['TL_LANG']['tl_grounding_page']['col_relationName'] = 'Name';
$GLOBALS['TL_LANG']['tl_grounding_page']['col_relationUrl'] = 'URL';

$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['organization'] = 'Organisation';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['person'] = 'Person';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['group-or-role'] = 'Gruppe oder Rolle';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['product'] = 'Produkt';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['service'] = 'Dienstleistung';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['tool-or-platform'] = 'Tool oder Plattform';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['feature'] = 'Merkmal';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['segment'] = 'Segment';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['field-of-knowledge'] = 'Wissensgebiet';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['concept'] = 'Konzept';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['publication'] = 'Publikation';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['dataset'] = 'Datensatz';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['standard'] = 'Standard';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['method'] = 'Methode';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['place'] = 'Ort';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['event'] = 'Veranstaltung';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['metric'] = 'Kennzahl';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['project'] = 'Projekt';
