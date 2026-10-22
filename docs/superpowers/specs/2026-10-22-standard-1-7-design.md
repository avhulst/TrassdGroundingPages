# Design: Grounding Page Standard v1.7

- **Datum:** 2026-10-22
- **Bundle:** `trassd/contao-grounding-pages`
- **Branch:** `feature/standard-1.7`
- **Quelle:** <https://groundingpage.com/spec/>, `/spec/changelog/`, `/spec/technical-implementation/`,
  `/human-verified/` (Stand: v1.7 vom 9. Oktober 2026)

## 1. Ziel und Kontext

Das Bundle implementiert heute Standard v1.6.1. v1.7 ändert weder Schema noch Ontologie, Seitenstruktur
oder Score-Schwellen. Neu ist das **menschliche Fakten-Review**:

1. Jede Grounding Page wird vor der Veröffentlichung von einem Menschen geprüft und freigegeben.
   Inhaltliche Änderungen an Fakten brauchen vor der Veröffentlichung ein neues Review.
2. Reviewer ist eine Person oder eine verantwortliche Organisation. Beide Varianten sind gleichwertig
   konform. Wird eine Organisation genannt, muss die prüfende Person intern nachvollziehbar bleiben.
3. Am `WebPage`-Objekt stehen `reviewedBy` (`Person` oder `Organization` mit `name`) und `lastReviewed`
   (`YYYY-MM-DD`). `dateModified` bleibt das Datum der letzten Änderung und ist vom Review getrennt.
4. Ein sichtbarer Review-Hinweis zeigt Reviewer und Datum, identisch zum JSON-LD. Mindestform:
   `Facts reviewed by [Reviewer] · Last reviewed by a human: <time datetime="YYYY-MM-DD">[date]</time>`.
5. Jede Seite nennt die Version, die sie tatsächlich erfüllt. Ohne dokumentiertes Review bleibt sie eine
   gültige v1.6-Seite. Bis zum ersten Review gibt es keinen Review-Hinweis und keinen Platzhalter.
6. Bestehende „Geprüft“-Daten ohne dokumentiertes Review dürfen nicht in Review-Daten umgewandelt werden.
7. Das Review-Datum ändert sich nur nach einem dokumentierten Review, nie durch Build, Deploy oder
   automatische Prüfungen.

Abgleich mit dem Bundle:

| Punkt | Stand | Handlungsbedarf |
|---|---|---|
| Reviewer und Review-Datum | fehlen | drei neue Felder, JSON-LD, sichtbarer Hinweis |
| `dateVerified` | Label „Zuletzt geprüft“, ausgegeben als `dateModified`, Footer „Zuletzt geprüft: …“ | Umdeutung zu „Zuletzt aktualisiert“, ohne Migration |
| Standardversion | eine globale Konstante `VERSION = '1.6.1'` | pro Seite abgeleitet: 1.7 mit Review, sonst 1.6.1 |
| Veröffentlichungsdatum | nur `dateCreated` | zusätzlich `datePublished` (Datumstabelle der Spec) |
| „Hinweis für menschliche Leser“ | vorhanden, abschaltbar | keiner, das ist kein Review-Hinweis |

**Ziel:** Mit dem Bundle lassen sich v1.7-konforme Grounding Pages pflegen. Seiten ohne Review bleiben
korrekt ausgewiesene v1.6.1-Seiten.

**Erfolgskriterien**

- Reviewer-Typ, Reviewer-Name und Review-Datum sind im Backend pflegbar und werden beim Speichern
  validiert.
- Bei einer geprüften Seite erscheinen der sichtbare Review-Hinweis, `reviewedBy`, `lastReviewed` und
  im Footer „v1.7“. Name und Datum sind in Markup und JSON-LD identisch.
- Bei einer ungeprüften Seite gibt es keinen Review-Hinweis, keine Review-Properties und im Footer
  „v1.6.1“.
- `dateVerified` wird nirgends als Review-Datum verwendet.
- Bestehende Datensätze funktionieren ohne Migration und ohne Datenänderung.
- `composer all`, `ecs check` und `rector --dry-run` laufen grün. Die E2E-Tests für Grounding Pages
  laufen grün.

**Nicht im Umfang** (bewusst entschieden):

- Badge „Human Verified“ (optional laut Spec, kann später ohne Datenänderung ergänzt werden).
- Internes Review-Protokoll, Checkliste der sechs Prüfpunkte, Verknüpfung mit Backend-Benutzern. Die
  interne Nachvollziehbarkeit läuft über die Contao-Versionierung (`enableVersioning`).
- Automatische Erkennung inhaltlicher Änderungen, Backend-Warnungen oder automatisches Zurückstufen.
- Ablauf des Reviews nach 12 Monaten (die Spec bindet diese Frist an Badge und Grounding Star).
- Grounding Star.

## 2. Datenmodell und Backend

### 2.1 Standardversion

`src/GroundingStandard.php` bleibt die einzige Quelle für Versionsangaben:

- `BASE_VERSION = '1.6.1'`: Version einer Seite ohne dokumentiertes Review.
- `REVIEWED_VERSION = '1.7'`: Version einer Seite mit dokumentiertem Review.
- `SPEC_URL = 'https://groundingpage.com/spec/'` bleibt.
- Die Konstante `VERSION` entfällt. Alle Verwendungen werden umgestellt.
- `versionFor(bool $reviewed): string` liefert die passende Version.
- `templateData(bool $reviewed = false): array{version: string, specUrl: string}`.

Die Twig-Funktion `grounding_standard` reicht den optionalen Parameter durch:
`grounding_standard(reviewed = false)`. Ohne Argument liefert sie 1.6.1. Ein überschriebenes Template,
das die Funktion ohne Argument aufruft, behauptet also nie fälschlich 1.7.

### 2.2 Review-Felder

Neue Felder in `tl_grounding_page`. Sie stehen in einer eigenen Legende `{review_legend}` direkt nach
`{governance_legend}` und vor `{publish_legend}`:

| Feld | Typ | SQL | eval |
|---|---|---|---|
| `reviewerType` | select, Optionen `person`, `organization` | `varchar(16) NOT NULL default 'person'` | keine leere Option, `w50` |
| `reviewerName` | text | `varchar(255) NOT NULL default ''` | `maxlength 255`, `w50` |
| `lastReviewed` | text, Datum | `int(10) unsigned NULL` | `rgxp date`, `datepicker`, `w50 wizard` |

- Die Optionen von `reviewerType` werden über `reference` in den Sprachdateien übersetzt
  („Person“ / „Organisation“ bzw. „Person“ / „Organization“).
- Palette: `…;{governance_legend},…,hideHumanNotice;{review_legend},reviewerType,reviewerName,lastReviewed;{publish_legend},published`.

**Hilfetexte** (de/en sinngemäß gleich):

- `reviewerName`: „Person oder Organisation, die die Fakten geprüft und freigegeben hat. Bei einer
  Organisation muss die prüfende Person intern nachvollziehbar bleiben.“
- `lastReviewed`: „Nur nach einem dokumentierten Fakten-Review durch einen Menschen setzen. Inhaltliche
  Änderungen an Fakten brauchen vor der Veröffentlichung ein neues Review. Tippfehler, Layout und
  Umformulierungen ohne neue Fakten zählen nicht dazu.“
- Legende: Sie erklärt, dass die Seite erst mit Name und Datum als v1.7 ausgewiesen wird.

### 2.3 Validierung

Ein neuer Service `src/EventListener/DataContainer/ReviewFieldsListener.php` hängt per
`#[AsCallback]` als `save_callback` an `reviewerName` und `lastReviewed`:

- Name und Datum gibt es nur zusammen oder gar nicht. Den Wert des jeweils anderen Felds liest der
  Listener aus dem aktuellen Request (`Input::post`), bei fehlendem POST-Wert aus `$dc->activeRecord`.
  Fehlt einer der beiden Werte, wird eine `\RuntimeException` mit übersetzter Meldung geworfen.
  Contao zeigt sie am Feld an.
- `lastReviewed` darf nicht in der Zukunft liegen (Vergleich auf Tagesebene).
- Die Prüflogik selbst liegt in einer reinen, framework-freien Klasse
  `src/Review/ReviewFieldsValidator.php`. Ihre Methode `validate(string $name, int|null $date, int $today):
  string|null` liefert einen Fehlerschlüssel oder `null`. Der Listener übersetzt den Schlüssel nur und
  wirft die Exception.
- Fehlermeldungen (de/en) unter `$GLOBALS['TL_LANG']['tl_grounding_page']['error']`.

### 2.4 Umdeutung von `dateVerified`

- Die Spalte und der Feldname bleiben unverändert, es gibt keine Migration.
- Das Label wird zu „Zuletzt aktualisiert“ / „Last updated“. Der Hilfetext lautet: „Datum der letzten
  inhaltlichen Änderung, ausgegeben als dateModified. Ohne Angabe gilt der Zeitpunkt der letzten
  Speicherung. Kein Review-Datum.“
- Die Fallback-Kette für `dateModified` (`dateVerified → tstamp → datePublished`) bleibt.
- Bestehende Werte werden ausdrücklich **nicht** in `lastReviewed` übernommen (Spec-Punkt 6).

### 2.5 DTO und Composer

- `GroundingPageDto` bekommt neue Parameter mit Defaults: `reviewerType = 'person'`,
  `reviewerName = ''` und `lastReviewed = null` (`string|null`, Format `Y-m-d`).
- Neue Methode `isReviewed(): bool`. Sie ist `true`, wenn `reviewerName !== ''` und
  `lastReviewed !== null`.
- `GroundingPageComposer::compose()` befüllt die Felder:
  - `reviewerName` getrimmt.
  - `lastReviewed` über das bestehende `formatDate()`.
  - Unbekannte `reviewerType`-Werte fallen auf `person` zurück. Die erlaubten Werte stehen in einem
    kleinen Enum `src/Enum/ReviewerType.php` (`Person = 'person'`, `Organization = 'organization'`,
    `schemaType()` liefert `Person` bzw. `Organization`, dazu `fromStringOrFallback()`). Die DCA nutzt
    dessen Werte als Optionen.

### 2.6 Datenbank

Es kommen nur neue Spalten mit Defaults hinzu. Das übernimmt der Schema-Diff von `contao:migrate`.
Bestandsseiten haben kein Review und bleiben v1.6.1-Seiten.

## 3. Ausgabe

### 3.1 Sichtbarer Review-Hinweis

- Neues Partial `contao/templates/grounding/_review_notice.html.twig`:

  ```twig
  {% if grounding.isReviewed %}
      <p class="grounding-page__review-notice">{{ 'grounding.review.notice'|trans([
          grounding.reviewerName|e,
          '<time datetime="' ~ grounding.lastReviewed|e ~ '">' ~ grounding.lastReviewed|e ~ '</time>'
      ], 'contao_default')|raw }}</p>
  {% endif %}
  ```

  Ohne Review entsteht kein Markup und kein Platzhalter.
- Es wird in `_standard_footer.html.twig` direkt vor `grounding-page__standard-text` eingebunden.
- Sprachschlüssel `$GLOBALS['TL_LANG']['grounding']['review']['notice']`:
  - de: `Fakten geprüft von %1$s · Zuletzt von einem Menschen geprüft: %2$s`
  - en: `Facts reviewed by %1$s · Last reviewed by a human: %2$s`
- Name und Datum stammen aus denselben DTO-Werten wie das JSON-LD und sind deshalb identisch.
- Der Controller übergibt das DTO als Objekt (`$template->set('grounding', $dto)`). Twig löst
  `grounding.isReviewed` deshalb direkt auf die Methode `isReviewed()` auf.
- `public/grounding.css`: Die Regel für `.grounding-page__review-notice` übernimmt Schrift und Abstand von
  `.grounding-page__standard-text`.

### 3.2 Standard-Hinweis im Footer

- `_standard_footer.html.twig` ruft `grounding_standard(grounding.isReviewed)` auf.
- Neuer Sprachschlüssel `grounding.detail.standardTextUpdated`:
  - de: `Diese Grounding Page folgt dem %1$s (v%2$s). Zuletzt aktualisiert: %3$s.`
  - en: `This Grounding Page follows the %1$s (v%2$s). Last updated: %3$s.`
- Der Footer verwendet `standardTextUpdated`. Der alte Schlüssel `standardTextVerified` bleibt mit
  demselben neuen Text bestehen, damit überschriebene Templates keinen rohen Schlüssel anzeigen.
  Im Code ist er als veraltet kommentiert.

### 3.3 JSON-LD

Am `WebPage` (`#webpage`) in `GroundingJsonLdBuilder::buildWebPage()`:

- `datePublished`: gleicher Wert wie `dateCreated`, nur wenn gesetzt. `dateCreated` bleibt.
- Nur wenn `$dto->isReviewed()`:
  - `lastReviewed`: `$dto->lastReviewed` (`Y-m-d`), per `setProperty` wie die übrigen Datumswerte.
  - `reviewedBy`: `{"@type": "Person" | "Organization", "name": reviewerName}`, gebaut über
    `ReviewerType::schemaType()`.
- `dateModified` bleibt unverändert.

Beispiel einer geprüften Seite:

```json
"dateCreated": "2026-01-15",
"datePublished": "2026-01-15",
"dateModified": "2026-10-01",
"lastReviewed": "2026-10-02",
"reviewedBy": { "@type": "Person", "name": "Jane Doe" }
```

Es gibt weiterhin genau einen `@graph`.

## 4. Fehlerbehandlung und Kompatibilität

- Alle neuen Felder sind optional. Ohne Name und Datum gibt es weder Markup noch Properties.
- Halbe Review-Angaben lassen sich im Backend nicht speichern (2.3). Kommen sie trotzdem in der Datenbank
  vor (Import, direkter SQL-Zugriff), behandelt das Bundle die Seite als ungeprüft.
- Alle neuen DTO-Parameter haben Defaults. Bestehende Konstruktor-Aufrufe und Tests kompilieren weiter.
- Bestandsseiten rendern bis auf zwei Dinge identisch: Im Footer steht „Zuletzt aktualisiert“ statt
  „Zuletzt geprüft“, und im JSON-LD kommt `datePublished` hinzu. Die Version bleibt v1.6.1.
- Wer `GroundingStandard::VERSION` außerhalb des Bundles verwendet, muss auf `versionFor()` oder eine der
  neuen Konstanten umstellen. Die Konstante war intern gedacht. Das kommt in die Release Notes.

## 5. Tests

Das Vorgehen ist TDD und folgt den bestehenden Testklassen.

| Test | Prüft |
|---|---|
| `GroundingStandardTest` | `BASE_VERSION === '1.6.1'`, `REVIEWED_VERSION === '1.7'`, `versionFor()`, `templateData(true/false)`. Die Guard-Regex erfasst auch `'1.7'`. |
| `Twig/GroundingStandardExtensionTest` | Ohne Argument kommt 1.6.1, mit `true` kommt 1.7. |
| `Enum/ReviewerTypeTest` (neu) | Werte, `schemaType()`, Fallback |
| `Review/ReviewFieldsValidatorTest` (neu) | Name ohne Datum und Datum ohne Name werden abgelehnt, ein Datum in der Zukunft wird abgelehnt, heute ist ok, beide leer oder beide gesetzt ist ok |
| `EventListener/DataContainer/ReviewFieldsListenerTest` (neu) | Exception mit übersetzter Meldung, Wert unverändert zurückgegeben |
| `Dca/TlGroundingPageDcaTest` | drei neue Felder, `{review_legend}` nach Governance, SQL-Defaults, Optionen aus dem Enum |
| `Dto/GroundingDtoTest` | Defaults, `isReviewed()` |
| `Composer/GroundingPageComposerTest` | Mapping der Review-Felder, Fallback beim Typ. `dateVerified` landet nie in `lastReviewed`. |
| `JsonLd/GroundingJsonLdBuilderTest` | `reviewedBy` (Person/Organization) und `lastReviewed` nur bei Review, `datePublished` neben `dateCreated` |
| `JsonLd/GroundingJsonLdSchemaValidationTest` | neue Properties valide am `WebPage`, weiterhin genau ein Graph |
| `Twig/ReviewNoticeTemplateTest` (neu) | Hinweis mit `<time datetime>`, Escaping des Namens, ohne Review kein Markup |
| `Twig/StandardFooterTemplateTest`, `FrontendTextTest` | v1.7 mit und v1.6.1 ohne Review, „Zuletzt aktualisiert“, Review-Hinweis vor dem Standard-Text |
| `JsonLd/CernReferenceTest` | Referenzfall um ein Review erweitert |

**E2E** (Root-Projekt, `e2e/fixtures/grounding-pages.ts`, `e2e/tests/grounding-pages.spec.ts`):

- Die Fixture-Seite bekommt `reviewerType`, `reviewerName` und `lastReviewed`.
- Der Spec prüft:
  - den sichtbaren Hinweis
  - `reviewedBy` und `lastReviewed` am WebPage-Knoten
  - ob Name und Datum im Markup und im JSON-LD identisch sind
  - den Footer mit „v1.7“
- Die bestehende Prüfung `dateModified === dateVerified` bleibt.

## 6. Qualitätssicherung

1. `composer all` (rector dry-run → ecs fix → phpstan → depcheck → phpunit).
2. CI-Formen ohne Mutation: `vendor/bin/ecs check`, `vendor/bin/rector --dry-run`.
3. `contao:migrate --no-interaction` im Root-Projekt und `ddev e2e` für die Grounding-Pages-Specs.
4. Review: `contao-dca-linter` für `tl_grounding_page.php`, `contao-extension-reviewer` für den
   gesamten Diff.

## 7. Doku und Release

- README und README_DE:
  - „unterstützt Standard v1.7“
  - ein Abschnitt zum menschlichen Review: Felder, Validierung, Regel zu inhaltlichen Änderungen,
    Version pro Seite (1.7 mit, 1.6.1 ohne Review), Hinweis zur Organisation als Reviewer
  - der geänderte Sinn von „Zuletzt aktualisiert“
  - `reviewedBy`, `lastReviewed` und `datePublished` im JSON-LD-Absatz
- Die `composer.json`-Description sowie `contao/languages/{de,en}/default.php` (`CTE`) und
  `contao/languages/{de,en}/modules.php` (`MOD`) auf v1.7 umstellen.
- `CLAUDE.md` im Root-Projekt: Bundle-Beschreibung auf v1.7 aktualisieren.
- Release als **Minor-Version 1.1.0**. Den Tag setzt der Maintainer manuell.
