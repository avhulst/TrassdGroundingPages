# Design: Grounding Page Standard 2.0

- **Datum:** 2026-11-23
- **Bundle:** `trassd/contao-grounding-pages`
- **Branch:** `feature/standard-2.0`
- **Zielversion des Bundles:** 1.2.0 (Minor, keine Breaking Changes)
- **Quelle:** <https://groundingpage.com/spec/>, `/spec/changelog/`, `/spec/technical-implementation/`,
  `/spec/entity-ontology/`, `/human-verified/` (Stand: Standard 2.0 vom 26. Oktober 2026, redaktionell
  aktualisiert am 27. Oktober 2026)

## 1. Ziel und Kontext

Das Bundle implementiert heute Standard v1.7 (1.6.1 ohne Review). Standard 2.0 übernimmt die Inhalte von
v1.7 als **Teil A, Seitenqualität (P-1 bis P-16)** und ergänzt den verbindlichen **Teil B,
Referenzintegrität (RI-1 bis RI-7)**. Eine Seite ist nur dann 2.0-konform, wenn sie alle MUSS-Anforderungen
beider Teile erfüllt und ein dokumentiertes **7-Punkte-Review** hat (neuer Punkt 7: Referenzen).

Relevante Regeln aus Teil B:

- **RI-1:** Jeder Entitätsknoten hat eine absolute URI in `@id`. Sie ist zeitlich stabil, über alle
  Sprachversionen und alle eigenen Seiten zur selben Entität gleich und liegt im Namensraum des
  Herausgebers. Bewährte Form: Anker-URL plus Fragment (`https://example.com/about/#organization`).
- **RI-2:** Die Entität hängt über `mainEntity` (WebPage) bzw. `mainEntityOfPage` (Entität) an der Seite.
  Bei mehreren eigenen Seiten zur selben Entität ist genau eine der Referenzanker. `mainEntityOfPage`
  zeigt auf den Anker in der jeweiligen Sprache. Nicht-Anker-Seiten verlinken den Anker sichtbar. Eine
  einzelne Seite ist automatisch der Anker.
- **RI-3:** `sameAs` nur für offizielle Profile/Websites, Einträge in Identitätsregistern (Wikidata, ISNI,
  ORCID, GND, LEI, Handelsregister) oder Nachschlagewerk-Einträge speziell zu dieser Entität. Nicht:
  eigene Seiten, Such-/API-/Listenseiten, Artikel, Studien (DOI/arXiv), Abschnitte fremder Einträge. Kein
  Minimum.
- **RI-4:** Jedes `sameAs` und jedes `identifier` der Hauptentität erscheint sichtbar (als `href` bzw.
  Wert im HTML).
- **RI-5:** Offizielle Profile sind von Berichten Dritter unterscheidbar.
- **RI-E2:** `identifier` als `PropertyValue`.

Kompatibilität laut Spec: v1.6- und v1.7-Konformitätsangaben bleiben gültig. **Keine Seite wird
automatisch hochgestuft.** Erst nach dokumentiertem 7-Punkte-Review darf der Standard-Hinweis 2.0 nennen.

Abgleich mit dem Bundle:

| Anforderung | Stand 1.1.0 | Handlungsbedarf |
|---|---|---|
| Versionsentscheidung | Review → 1.7, sonst 1.6.1 | Feld „Geprüft nach Standard“, 2.0 nur explizit und bei bestandenen RI-Prüfungen |
| RI-1 `@id` | relativ (`#webpage`, `#main`), Parent und Terms ohne `@id` | absolute, stabile IDs |
| RI-2 Anker | `mainEntityOfPage` → `#webpage` | Anker-URL, sichtbarer Anker-Link auf Nicht-Anker-Seiten |
| RI-3 `sameAs` | beliebige URLs | `SameAsPolicy`, Validierung beim Speichern und Rendern |
| RI-4 Sichtbarkeit | Seitenfeld `sameAs` wird nicht angezeigt | Block „Offizielle Profile und Kennungen von X“ |
| RI-5 Trennung | Kennungen und Quellen in einer Sektion | Kennungen in eigenen Block, Quellen-Sektion nur Quellen |
| RI-E2 `identifier` | Kennungen in `additionalProperty` | `identifier` als `PropertyValue` |
| P-3 Name in H2 | „Abgrenzung“, „Quellen & Vertrauenssignale“, „Änderungsprotokoll“ ohne Namen | Standard-Überschriften mit Entitätsnamen |
| Leser-Hinweis | standardmäßig sichtbar | bei 2.0-Seiten ausgeblendet |

**Ziel:** Mit dem Bundle lassen sich 2.0-konforme Grounding Pages pflegen. Bestehende Seiten bleiben ohne
Datenänderung korrekt als 1.7 bzw. 1.6.1 ausgewiesen.

**Erfolgskriterien**

- Eine geprüfte Seite mit „Geprüft nach Standard 2.0“ und regelkonformen Referenzen nennt im Footer
  „Grounding Page Standard 2.0“, hat absolute `@id`s, einen korrekten Anker und zeigt jedes `sameAs` und
  jede Kennung sichtbar an.
- Bestehende geprüfte Datensätze (Feld leer) bleiben 1.7, ungeprüfte bleiben 1.6.1.
- Ein RI-3-Verstoß blockiert bei 2.0 das Speichern. Ein zur Laufzeit erkannter Verstoß stuft die Seite
  auf 1.7 herunter und wird geloggt. Die Seite behauptet nie fälschlich 2.0.
- Bestehende Datensätze funktionieren ohne Migration.
- `composer all`, `ecs check` und `rector --dry-run` laufen grün. Die Playwright-Tests (über Browserless)
  für Grounding Pages laufen grün, ebenso `npm run typecheck` und die Unit-Tests im E2E-Projekt.

**Nicht im Umfang** (bewusst entschieden):

- Badge „Human Verified“ (P-14, MAY).
- Review-Protokoll, Checkliste der sieben Prüfpunkte, Abweichungsfälle (RI-7) im Backend. Die interne
  Nachvollziehbarkeit läuft weiter über die Contao-Versionierung.
- Erreichbarkeitsprüfung von `sameAs`-URLs, Allowlist offizieller Profile.
- `subjectOf` für unabhängige Berichte (RI-E6), `AboutPage`/`ProfilePage` als Seitentyp, `rel="me"`.
- Grounding Star, Review-Ablauf nach 12 Monaten.
- Aktualisierung der PDF-Redaktionsdoku (separat danach).

## 2. Datenmodell und Backend

### 2.1 Neue Felder in `tl_grounding_page`

| Feld | Legende | Definition | Bedeutung |
|---|---|---|---|
| `reviewStandard` | `review_legend` (nach `lastReviewed`) | `select`, Optionen `''`, `1.7`, `2.0`, `includeBlankOption`, `varchar(8) NOT NULL default ''` | „Geprüft nach Standard“. Leer gilt bei vorhandenem Review als 1.7. |
| `entityId` | `definition_legend` (vor `sameAs`) | `text`, `rgxp=url`, `decodeEntities`, `maxlength=2048`, `varchar(2048) NOT NULL default ''` | Optionaler Entity-Identifier (absolute http(s)-URI). Leer bedeutet automatisch. |
| `anchorUrl` | `definition_legend` (nach `entityId`) | wie `entityId` | Optionale URL der Referenzseite in dieser Sprache. Leer heißt: diese Seite ist der Anker. |

Hilfetexte (de/en) erklären den mehrsprachigen Fall: In allen Sprachdatensätzen derselbe `entityId`
(am besten mit der `x-default`-URL des Ankers), `anchorUrl` jeweils mit der Anker-URL der eigenen Sprache,
auf der Ankerseite selbst leer.

`reviewStandard` wird beim Kopieren wie die übrigen Review-Felder zurückgesetzt (`doNotCopy`).
`entityId` und `anchorUrl` werden mitkopiert, weil eine Kopie meist eine Sprachversion derselben Entität
ist.

Keine Migration: Alle neuen Felder sind leer, damit bleibt das bisherige Verhalten erhalten.

### 2.2 Versionskonstanten

`src/GroundingStandard.php` bleibt die einzige Quelle für Versionsnummern:

- `BASE_VERSION = '1.6.1'`, `REVIEWED_VERSION = '1.7'` bleiben.
- Neu: `V2_VERSION = '2.0'`.
- `versionFor(bool $reviewed)` bleibt als Fassade erhalten und liefert weiterhin höchstens 1.7.
- `templateData(bool $reviewed = false, ?string $version = null)`: Mit `$version` wird genau diese Version
  ausgegeben, sonst wie bisher `versionFor($reviewed)`. Die Twig-Funktion `grounding_standard()` reicht
  beides durch. Ein überschriebenes Template, das nur `grounding.isReviewed` übergibt, nennt also höchstens
  1.7, nie fälschlich 2.0.

### 2.3 Validierung beim Speichern

`ReviewFieldsValidator` und `ReviewFieldsListener` werden erweitert:

- `reviewStandard` ≠ leer ist nur zusammen mit Reviewer-Name und Review-Datum erlaubt.
- `entityId` und `anchorUrl` müssen, falls gesetzt, absolute http(s)-URIs sein.
- Bei `reviewStandard = '2.0'` prüft `SameAsPolicy` (Abschnitt 3.3) jede URL aus dem Feld `sameAs`. Ein
  Verstoß wirft eine Fehlermeldung pro URL mit Grund und blockiert das Speichern.
- Bei 1.7 oder ohne Review: gleiche Prüfung, aber nur `Message::addInfo` statt Fehler.
- „Eigene Domain“ heißt beim Speichern: der Host von `entityId` und `anchorUrl` sowie alle `dns`-Werte der
  Contao-Startseiten (`tl_page.type = 'root'`).

`tl_grounding_section` bekommt einen Save-Callback auf dem Kennungsfeld (`SectionRowSchema::Identifiers`).
Er prüft URL-Werte nach derselben Regel, sobald der Elterndatensatz `reviewStandard = '2.0'` hat (Fehler),
sonst nur Hinweis.

## 3. Auflösungslogik und JSON-LD

Neues Modul `src/Reference/` mit drei Klassen, jede einzeln testbar.

### 3.1 `EntityIdentityResolver`

`resolve(GroundingPageDto $dto, string $pageUrl): EntityIdentity`, wobei `EntityIdentity` ein readonly DTO
mit `pageUrl`, `entityId`, `anchorUrl` und `isAnchor` ist.

- `pageUrl`: kanonische absolute URL der aktuellen Contao-Seite. Der Controller ermittelt sie über den
  `ContentUrlGenerator` (`UrlGeneratorInterface::ABSOLUTE_URL`, ohne Query-String) und gibt sie mit.
- `entityId`: Feld `entityId`, falls gesetzt. Sonst Anker-URL (Feld `anchorUrl`, ersatzweise `pageUrl`) +
  `#` + Typ-Fragment: der Schema-Typ in
  Kleinbuchstaben (`#organization`, `#person`, `#softwareapplication`), bei `Thing` `#entity`.
- `anchorUrl`: Feld `anchorUrl`, falls gesetzt (`isAnchor = false`), sonst `pageUrl` (`isAnchor = true`).

Der automatische Identifier ist nur stabil, solange der Datensatz auf genau einer Seite in einer Sprache
eingebunden ist. Bei mehreren Seiten oder Sprachversionen muss die Redaktion `entityId` setzen. Das sagt
der Hilfetext. Ist `anchorUrl` gesetzt und `entityId` leer, wird der Identifier aus `anchorUrl` statt aus
`pageUrl` gebildet, damit Nicht-Anker-Seiten automatisch dieselbe `@id` wie der Anker tragen.

### 3.2 `ConformanceResolver`

`resolve(GroundingPageDto $dto, EntityIdentity $identity): ConformanceResult`, mit `version` und einer
Liste der Verstöße.

1. Ohne Review (`isReviewed()` false) ergibt sich **1.6.1**.
2. Mit Review und `reviewStandard` ≠ `2.0` ergibt sich **1.7**.
3. Bei `2.0` laufen die Laufzeitprüfungen: `entityId` ist absolut, und jede `sameAs`-URL der Hauptentität
   (Seitenfeld und URL-Kennungen) besteht `SameAsPolicy` mit eigenen Hosts = Host von `pageUrl` und
   `entityId`. Bei Erfolg ergibt sich **2.0**, sonst **1.7** plus ein Warn-Eintrag im Contao-Log (ID des
   Datensatzes, Verstöße).

RI-4 erfüllt das Template strukturell (Abschnitt 4.1), dafür gibt es keine Laufzeitprüfung.

### 3.3 `SameAsPolicy`

`check(string $url, list<string> $ownHosts): ?SameAsViolation`, eine reine Funktion. `SameAsViolation`
ist ein Enum mit Übersetzungsschlüssel:

| Grund | Erkennung |
|---|---|
| `NotHttp` | Schema nicht http/https oder kein Host |
| `OwnDomain` | Host gleich einem eigenen Host oder Subdomain davon (`www.` normalisiert) |
| `SearchOrApi` | Pfad enthält `/search`, `/api/`, `api.php`, `Special:Search`, oder Query hat `q`, `s`, `query`, `search` |
| `DoiOrArxiv` | Host `doi.org`, `dx.doi.org`, `arxiv.org` |
| `SectionAnchor` | URL hat ein nicht leeres Fragment |

`null` heißt: die URL ist in Ordnung. Es gibt keine Allowlist. Ob ein Profil offiziell ist, beurteilt das
menschliche Review.

### 3.4 Änderungen am Graphen (`GroundingJsonLdBuilder`)

Der Builder bekommt `EntityIdentity` zusätzlich zum DTO.

- **WebPage:** `@id` = `pageUrl`, `url` = `pageUrl`, `mainEntity` = `{"@id": entityId}`. Die übrigen
  Properties bleiben unverändert (`reviewedBy`, `lastReviewed`, `datePublished`, `dateModified`,
  `citation`, `relatedLink`, …).
- **Hauptentität:** `@id` = `entityId`, `mainEntityOfPage` = `anchorUrl` als URL-String. Nicht-URL-Kennungen
  stehen als `identifier` (Liste von `PropertyValue` mit `propertyID` und `value`) statt in
  `additionalProperty`. Das gilt für alle Versionen. URL-Kennungen und das Feld `sameAs` bleiben in
  `sameAs`.
- **Übergeordnete Entität:** Mit `parentEntityUrl` ein Knoten mit `@id` = `parentEntityUrl`, `url` und
  `name`. Ohne URL kein eigener Entitätsknoten mehr, die Angabe steht dann als `PropertyValue`
  `parentEntity` in `additionalProperty`.
- **DefinedTerm-Knoten:** `@id` = `pageUrl#term-<slug>` (Slug aus dem Begriff, bei Kollision mit Index).
- **FAQPage:** `@id` = `pageUrl#faq`.

Die Graph-Schlüssel (`grounding-main`, `grounding-term-N`, `grounding-faq`) und das Überschreiben der vom
Core geseedeten WebPage per `set()` bleiben.

### 3.5 Datenfluss

`GroundingPageController`: Datensatz laden → `GroundingPageComposer::compose()` → `pageUrl` ermitteln →
`EntityIdentityResolver` → `ConformanceResolver` → `GroundingJsonLdBuilder::addToResponseContext($dto,
$identity)`. Ans Template gehen `grounding` (DTO), `identity` und `conformance`.

`showHumanNotice` ist bei `conformance.version == '2.0'` immer false. Das entscheidet der Controller, nicht
das Template.

## 4. Frontend

### 4.1 Block „Offizielle Profile und Kennungen“

Neues Template `grounding/_official_profiles.html.twig`, in `_page.html.twig` direkt vor dem Standard-Footer
eingebunden und nur gerendert, wenn es mindestens eine `sameAs`-URL oder Kennung gibt.

- H2 „Offizielle Profile und Kennungen von %name%“ (`grounding.detail.officialProfiles`).
- `sameAs`-URLs als `<ul>` mit `<a href="…" rel="noopener">`, Linktext ist die URL.
- Kennungen als `<dl class="data-grid">` mit Label und Wert. URL-Werte werden verlinkt.
- Klasse `.grounding-page__profiles`.

Die Quelle der angezeigten Werte ist dieselbe Sammelfunktion wie im Builder (`sameAs` plus Kennungen aller
Sektionen), damit Markup und JSON-LD nicht auseinanderlaufen. Sie liegt als öffentliche Methode am DTO
(`officialProfiles()`, `identifiers()`).

### 4.2 Quellen-Sektion und Überschriften

- `_sources.html.twig` zeigt keine Kennungen mehr, nur noch Quellen. Standard-H2: „Quellen zu %name%“
  (`grounding.detail.sources`). Ein redaktioneller Sektionstitel überschreibt sie weiterhin.
- P-3, Standard-Überschriften mit Namen (de/en): „Abgrenzung von %name%“, „Änderungen an %name%“ (Changelog
  im Footer). „Weiterführende Literatur zu %name%“ hat ihn bereits.
- Hilfetext am Sektionstitel: „Entitätsnamen in die Überschrift aufnehmen (Standard 2.0, P-3).“
- `section.identifiers` bleibt im DTO, damit überschriebene `_sources`-Templates weiter funktionieren.

### 4.3 Anker-Link

Bei `identity.isAnchor == false` steht im Header nach der Leitdefinition:
`<p class="grounding-page__anchor-link"><a href="{anchorUrl}">Offizielle Informationen zu %name%</a></p>`
(`grounding.detail.anchorLink`).

### 4.4 Footer und Hinweise

- Footer: `grounding_standard(grounding.isReviewed, conformance.version)`. Die Übersetzungsstrings nennen
  die Version ohne festes Präfix, also „Grounding Page Standard 2.0“ bzw. „… (v1.7)“ wie bisher.
- Der Review-Hinweis bleibt unverändert, die Labels entsprechen der Spec.
- Leser-Hinweis siehe 3.5.

### 4.5 CSS

`public/grounding.css` bekommt Regeln für `.grounding-page__profiles` und `.grounding-page__anchor-link` im
bestehenden Stil.

## 5. Tests

### 5.1 PHPUnit (TDD)

- `tests/Reference/SameAsPolicyTest.php`: Data Provider mit gültigen URLs (Wikidata, LinkedIn, ORCID, GND)
  und mindestens einem Fall pro Verstoßgrund.
- `tests/Reference/EntityIdentityResolverTest.php`: automatischer Identifier je Schema-Typ, explizites
  `entityId`, Anker- und Nicht-Anker-Fall.
- `tests/Reference/ConformanceResolverTest.php`: 1.6.1, 1.7 (leeres Feld), 1.7 (explizit), 2.0, 2.0 mit
  Laufzeitverstoß → 1.7 plus Log.
- Anpassen: `GroundingJsonLdBuilderTest`, `GroundingJsonLdSchemaValidationTest`, `CernReferenceTest`
  (absolute `@id`s, `mainEntityOfPage`, `identifier`, Parent-Knoten), `ReviewFieldsValidatorTest`,
  `ReviewFieldsListenerTest` (2.0 blockiert, 1.7 nur Hinweis), `TlGroundingPageDcaTest` (neue Felder,
  `doNotCopy`), `GroundingStandardTest`, `GroundingStandardExtensionTest`, Controller-Tests.
- Neue Template-Tests: Profile-Block, Quellen ohne Kennungen, Anker-Link, H2 mit Namen, Leser-Hinweis bei
  2.0, Footer-Version.

### 5.2 E2E (Playwright über Browserless)

In `e2e/fixtures/grounding-pages.ts` und `e2e/tests/grounding-pages.spec.ts`, IDs ≥ 900000, Aliase `e2e-*`:

| Seite | Inhalt | Erwartung |
|---|---|---|
| (a) 2.0-Anker | geprüft, `reviewStandard = 2.0`, `sameAs` und Kennungen | absolute `@id`s im Graphen, jede `sameAs`-URL als sichtbares `href`, Footer „2.0“, kein Leser-Hinweis, H2s mit Namen |
| (b) Nicht-Anker | `anchorUrl` auf (a), gleicher `entityId` | `mainEntityOfPage` = Anker, sichtbarer Anker-Link, gleiche Entity-`@id` wie (a) |
| (c) Altbestand | geprüft, `reviewStandard` leer | Footer „1.7“ |
| (d) Laufzeitverstoß | `reviewStandard = 2.0`, `sameAs` auf die eigene Domain (per SQL, am Validator vorbei) | Footer „1.7“ |

Assertions auf Graph-Knoten über `e2e/lib/jsonld.ts`. Voraussetzung: Browserless läuft (`ddev browserless
on` auf dem Host). Danach laufen `npm run typecheck`, `npm run test:unit` und `npm run test:integration` im
E2E-Projekt.

## 6. Werkzeuge, Doku, Release

- **Werkzeuge:**
  - context7 für Contao 5.7 (`ContentUrlGenerator`, DCA-Callbacks), spatie/schema-org und Playwright.
  - trassd-Skills für DCA, Twig-Templates, Testing, Quality-Tooling und Übersetzungen.
  - Abschließende Prüfung durch `contao-extension-reviewer` und `contao-dca-linter`.
- **Doku:**
  - README und README_DE: Abschnitt zu Standard 2.0 (Versionsentscheidung, Anker und Identifier,
    `sameAs`-Regeln, mehrsprachiger Fall).
  - Upgrade-Hinweise nach dem Muster der 1.7-Notes.
  - `composer.json`-Beschreibung auf „Standard v2.0“.
- **Release:** 1.2.0 (Minor). Commits nur nach Rückfrage, einzeilige englische Conventional Commits.
