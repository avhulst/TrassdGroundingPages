# Design: Grounding Page Standard v1.6.1

- **Datum:** 2026-10-08
- **Bundle:** `trassd/contao-grounding-pages`
- **Branch:** `feature/standard-1.6.1`
- **Quelle:** <https://groundingpage.com/spec/>, `/spec/changelog/`, `/spec/technical-implementation/`

## 1. Ziel und Kontext

Das Bundle implementiert heute Standard v1.6. Aktuell ist v1.6.1. Laut Changelog ist 1.6.1 eine
**Klarstellung, keine Schema-Änderung**. Es stellt drei Punkte klar:

1. Pflichtblöcke legen fest, *welche Information* eine Seite enthält. Ihre Reihenfolge ist
   **empfohlen, nicht verpflichtend**, sofern ein Block nichts anderes sagt.
2. *Further Reading* steht im unteren Seitenbereich, darf aber vor oder nach FAQ und References
   stehen.
3. Klassifikationsangaben (entity type, industry/category, geographic scope, parent
   entity/organization, status, key relationships) sind als **Information** Pflicht, aber nicht
   zwingend als eigener sichtbarer Abschnitt.

Abgleich mit dem Bundle:

| Punkt | Stand | Handlungsbedarf |
|---|---|---|
| Freie Reihenfolge | Abschnitte sind per `sorting` frei sortierbar | keiner |
| Further Reading | Abschnittstyp fehlt (Lücke seit v1.6) | neuer Abschnittstyp |
| Klassifikation | `entityType`, `segment`, `status` vorhanden. geographic scope, parent, relationships fehlen | neue Kopffelder + JSON-LD |
| Versionsangabe | `'1.6'` viermal hart codiert im Footer-Template, dazu Texte in README, composer.json, Sprachdateien | zentrale Konstante, `1.6.1` |

**Ziel:** Das Bundle ist vollständig konform zu v1.6.1, inklusive der fehlenden v1.6-Blöcke.

**Erfolgskriterien**

- Es gibt einen Abschnittstyp „Further Reading", sichtbar gerendert und im JSON-LD gespiegelt.
- Alle sechs Klassifikationsangaben lassen sich pflegen. Sie erscheinen sichtbar in der Header-Meta-Liste
  und im JSON-LD.
- Quellen werden im JSON-LD gespiegelt (`citation`).
- Die Standardversion steht an genau einer Stelle im Code und lautet `1.6.1`.
- Bestehende Datensätze funktionieren ohne Migration und ohne Datenänderung.
- `composer all`, `ecs check` und `rector --dry-run` laufen grün.

**Nicht im Umfang:** Erzwingen einer Abschnittsreihenfolge, eigener Abschnittstyp für Klassifikation,
neue Entitätstypen, CI-Workflow für das Bundle.

## 2. Datenmodell und Backend

### 2.1 Zentrale Standardversion

- Neue Klasse `src/GroundingStandard.php` (`final`, nur Konstanten):
  - `VERSION = '1.6.1'`
  - `SPEC_URL = 'https://groundingpage.com/spec/'`
- Der `GroundingPageController` übergibt beide Werte als Template-Variable `standard`
  (`{version, specUrl}`) an das Content-Element-Template. `_page.html.twig` reicht sie an
  `_standard_footer.html.twig` durch.
- `_standard_footer.html.twig` nutzt `standard.version` und `standard.specUrl` statt der Literale
  `'1.6'` und der fest eingetragenen URL.
- In den Texten steht danach „v1.6.1": README, README_DE, `composer.json` (description),
  `contao/languages/{de,en}/default.php` (`CTE`) und `contao/languages/{de,en}/modules.php` (`MOD`).
- Das Beispiel im Label von `entryVersion` (Version des **Eintrags**) wird von „1.6" auf „1.0" geändert,
  damit es nicht mit der Standardversion verwechselt wird.

### 2.2 Abschnittstyp Further Reading

- `GroundingSectionType::FURTHER_READING = 'further-reading'`. `fields()` liefert
  `[SectionRowSchema::FurtherReading]`.
- `SectionRowSchema::FurtherReading = 'furtherReading'`. `columns()` liefert `['title', 'url']`.
- `tl_grounding_section`: Neues Feld `furtherReading` mit `inputType` `rowWizard` und `sql` blob, analog zu
  `sources`. Die Subpalette `sectionType_further-reading` entsteht automatisch über die bestehende
  Schleife.
- `GroundingSectionDto`: neuer Parameter `array $furtherReading = []`.
- `GroundingPageComposer::composeSection()`: `furtherReading: $this->rows($section, $type,
  SectionRowSchema::FurtherReading)`.
- Sprachdateien (de/en): Optionstext des Abschnittstyps, Feld-Label, Spaltenlabels.
- Die Position ist frei sortierbar. Es gibt keine erzwungene Platzierung (1.6.1).

### 2.3 Klassifikationsfelder

Neue optionale Felder in `tl_grounding_page`. In der Palette stehen sie in `{definition_legend}` direkt nach
`segment`:

| Feld | Typ | SQL | eval |
|---|---|---|---|
| `geographicScope` | text | `varchar(255) NOT NULL default ''` | `maxlength 255`, `w50` |
| `parentEntity` | text | `varchar(255) NOT NULL default ''` | `maxlength 255`, `w50` |
| `parentEntityUrl` | text | `varchar(2048) NOT NULL default ''` | `rgxp url`, `w50` |
| `relationships` | rowWizard | `blob NULL` | Spalten `relation`, `name`, `url`; `clr` |

- Die Spalten-Keys von `relationships` (`relation`, `name`, `url`) werden wie beim bestehenden
  Kopffeld `changelog` als Literale in DCA und Composer geführt. `SectionRowSchema` bleibt den
  Abschnittsfeldern vorbehalten.
- `GroundingPageDto`: neue Parameter mit Defaults: `geographicScope = ''`, `parentEntity = ''`,
  `parentEntityUrl = ''`, `relationships = []` (`list<array<string,string>>`).
- `GroundingPageComposer::compose()`: befüllt die neuen Parameter.
  - `pairs(mixed $raw, string $keyA, string $keyB)` wird zu `rowsOf(mixed $raw, string ...$keys)`
    verallgemeinert. Das Verhalten bleibt gleich: trimmen, Zeilen mit nur leeren Werten verwerfen.
    Alle bestehenden Aufrufer werden umgestellt.
  - `relationships` liest `rowsOf($page->relationships, 'relation', 'name', 'url')` und verwirft
    zusätzlich Zeilen, in denen `relation` oder `name` leer ist.
- Sprachdateien (de/en) für alle Labels.

### 2.4 Datenbank

Es kommen nur neue Spalten mit Defaults hinzu. Das übernimmt der Schema-Diff von `contao:migrate`, eine eigene
Migration ist nicht nötig. Bestehende Datensätze ändern sich nicht.

## 3. Ausgabe

### 3.1 Templates

- **`grounding/section/_further_reading.html.twig`** (neu):
  - `<section class="grounding-page grounding-page__further-reading">` mit H2.
  - Die H2 lautet `section.sectionTitle`, sonst der Fallback `grounding.detail.furtherReading` mit dem
    Entitätsnamen („Weiterführende Literatur zu %s" bzw. „Further reading on %s"). Damit steht der
    Entitätsname in der inhaltstragenden H2, wie die Spec es verlangt.
  - Die Liste der Einträge ist verlinkt, wenn die URL http(s) ist, sonst steht nur der Titel als Text.
    Zeilen ohne Titel und ohne URL entfallen. Ohne Titel dient die URL als Linktext.
  - `rel="noopener"` wie bei den Quellen.
- **`_page.html.twig`**: neuer `elseif section.sectionType == 'further-reading'`-Zweig. Er übergibt
  `{ section: section, name: grounding.name }` mit `only`.
- **`section/_entity_header.html.twig`**: Die bestehende `<dl class="grounding-page__meta">` bekommt
  neue Zeilen, ohne eigenen Abschnitt:
  - „Geografischer Bereich" / „Geographic scope": `geographicScope`
  - „Übergeordnet" / „Parent": `parentEntity`, verlinkt mit `parentEntityUrl`, falls gesetzt
  - „Beziehungen" / „Relationships": je Beziehung ein `<dd>` „relation: name", verlinkt mit `url`,
    falls gesetzt
  - Die `if`-Bedingung um das `dl` wird um die neuen Felder erweitert.
- **`_standard_footer.html.twig`**: siehe 2.1.
- **`public/grounding.css`**: `.grounding-page__further-reading a` bekommt dieselben Regeln wie
  `.grounding-page__sources a` (als gemeinsamer Selektor).

### 3.2 JSON-LD

Es gilt das Mirroring-Prinzip: Alles Sichtbare wird gespiegelt, nichts Unsichtbares kommt hinzu.

**An `WebPage` (`#webpage`)**

- `citation`: eine Liste von `CreativeWork` mit `name` (Titel) und `url` (nur wenn http(s)). Die Daten stammen aus allen
  `sources`-Zeilen. Zeilen ohne Titel und ohne gültige URL werden übersprungen. Fehlt der Titel, dient
  die URL als `name`. Die Property wird nur gesetzt, wenn die Liste nicht leer ist.
- `relatedLink`: eine Liste von URL-Strings aus allen `furtherReading`-Zeilen. Es kommen nur http(s)-URLs
  hinein, Duplikate werden entfernt. Die Property wird nur gesetzt, wenn die Liste nicht leer ist.

**An der Haupt-Entität (`#main`)**

Ein neuer reiner Resolver `src/JsonLd/ClassificationPropertyMap.php` hat keine Framework-Abhängigkeit.
Er entscheidet anhand des aufgelösten `@type`-Strings, welche Property verwendet wird:

| Angabe | Organization | Service | CreativeWork, Dataset | alle anderen und `customSchemaType` |
|---|---|---|---|---|
| `geographicScope` | `areaServed` (Text) | `areaServed` (Text) | `spatialCoverage` (Text) | `PropertyValue {name: "geographicScope", value}` |
| `parentEntity` | `parentOrganization` → `Organization {name, url?}` | Fallback | `isPartOf` → `CreativeWork {name, url?}` | `PropertyValue {name: "parentEntity", value: name, url?}` |

- Ist `customSchemaType` gesetzt, greift **immer** der Fallback, weil die Typ-Hierarchie unbekannt ist.
- Schnittstelle: `resolve(string $schemaType, bool $isCustomType): array{geographicScope: string|null,
  parentEntity: string|null}`. Rückgabe ist der Property-Name, oder `null` für den Fallback in
  `additionalProperty`.
- `GroundingJsonLdBuilder::buildMainEntity()` nutzt den Resolver. Fallback-Werte landen in
  `buildAdditionalProperties()`.

Weitere Ergänzungen in `additionalProperty`:

- `relationships`: je Zeile `PropertyValue {name: relation, value: name}`, dazu `url`, wenn http(s).
- `segment`: `PropertyValue {name: "category", value: segment}`. Das ist bewusst additiv und neu (bisher
  nicht gespiegelt).
- Bestehende Einträge (`status`, `version`, Fakten, Identifikatoren, Timeline) bleiben unverändert.

## 4. Fehlerbehandlung und Kompatibilität

- Alle neuen Felder sind optional. Leere Werte erzeugen weder Markup noch Properties.
- URL-Prüfung: Wiederverwendet wird das bestehende `isUrl()` (nur `http://`/`https://`). Ungültige URLs
  werden im JSON-LD verworfen und im Template als reiner Text ausgegeben.
- Unbekannte `sectionType`-Werte verhalten sich unverändert (`fromStringOrFallback`).
- Alle neuen DTO-Parameter haben Defaults. Bestehende Konstruktor-Aufrufe und Tests kompilieren weiter.
- Bestehende Seiten rendern identisch. Im JSON-LD kommen nur `citation` (falls Quellen gepflegt
  sind) und `category` (falls ein Segment gesetzt ist) hinzu. Der Footer zeigt „v1.6.1".

## 5. Tests

Das Vorgehen ist TDD und folgt den bestehenden Testklassen.

| Test | Prüft |
|---|---|
| `Enum/GroundingSectionTypeTest` | neuer Case, `fields()`, `values()` |
| `Enum/SectionRowSchemaTest` | `FurtherReading`: Feldname und Spalten |
| `Dca/TlGroundingSectionDcaTest` | Feld `furtherReading` (rowWizard, blob), Subpalette |
| `Dca/TlGroundingPageDcaTest` | vier neue Felder, Platzierung nach `segment` |
| `Composer/GroundingPageComposerTest` | Mapping `furtherReading` und Klassifikation, leere Beziehungszeilen werden verworfen |
| `JsonLd/ClassificationPropertyMapTest` (neu) | Tabelle aus 3.2 inklusive Custom-Type-Fallback |
| `JsonLd/GroundingJsonLdBuilderTest` | `citation`, `relatedLink` (Dedupe, ungültige URLs), Klassifikation je Typ, `category`, Beziehungen |
| `JsonLd/GroundingJsonLdSchemaValidationTest` | neue Properties nur an für den Typ validen Stellen, weiterhin genau ein Graph |
| `JsonLd/CernReferenceTest` | Referenzfall erweitert um Further Reading und Klassifikation |
| `FrontendTextTest`, `Controller/GroundingControllersTest` | Footer „v1.6.1", Further-Reading-Partial, neue Meta-Zeilen |
| `GroundingStandardTest` (neu) | `VERSION === '1.6.1'`; kein Template unter `contao/templates` enthält ein Versions-Literal `'1.6'` |

## 6. Qualitätssicherung

1. `composer install` im Bundle (das lokale `vendor/` fehlt noch).
2. `composer all` (rector dry-run → ecs fix → phpstan → depcheck → phpunit).
3. CI-Formen ohne Mutation: `vendor/bin/ecs check`, `vendor/bin/rector --dry-run`.
4. Review: `contao-dca-linter` für beide DCA-Dateien, `contao-extension-reviewer` für den Gesamt-Diff.

## 7. Doku und Release

- README und README_DE bekommen:
  - „v1.6.1"
  - Further Reading in der Abschnittstabelle
  - die Klassifikationsfelder in der Backend-Beschreibung
  - im JSON-LD-Absatz `citation`, `relatedLink` und Klassifikation
  - einen Hinweis, dass die Abschnittsreihenfolge frei ist (1.6.1) und Further Reading im unteren Bereich
    empfohlen wird
- Release als **Minor-Version** (neue, rückwärtskompatible Features). Der Tag wird manuell durch den
  Maintainer gesetzt.
