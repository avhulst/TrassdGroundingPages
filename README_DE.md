# Contao Grounding Pages (`trassd/contao-grounding-pages`)

Integriert **Grounding Pages** nach dem *Grounding Page Standard v2.0*
(<https://groundingpage.com/>) in Contao. Eine Grounding Page ist ein faktischer, klar
strukturierter Wissenseintrag pro Entität — geschrieben für **Menschen und KI-/Antwortmaschinen**
(GEO/AEO). Das Bundle liefert dafür ein Backend-Modell, ein Frontend-Element und **genau ein**
standardkonformes JSON-LD-Dokument pro Seite.

Hintergründe und Praxis im Blogbeitrag:
[Grounding Pages in Contao](https://vanhulst.de/blog/grounding-pages-contao).

## Konzept

- **Ein Datensatz = eine Entität.** Eine Kopftabelle mit den Governance-Daten der Entität und eine
  sortierbare Kindtabelle mit den inhaltlichen Abschnitten.
- **Eingebettet in die Seite.** Die komplette Grounding Page wird als **Content-Element** in einem
  Artikel platziert und rendert im normalen Seiten-Layout (Header, Footer, Theme).
- **Volle Ontologie** (18 Entitätsklassen, abgebildet auf schema.org, Fallback `Thing`),
  erweiterbar über ein Freitextfeld für beliebige `@type`-Werte.

## Anforderungen

- PHP `^8.3`, Contao `^5.7`
- `spatie/schema-org` (`^3.10 || ^4.0`) — wird von Contao-Core bereitgestellt

## Installation

```bash
composer require trassd/contao-grounding-pages
vendor/bin/contao-console contao:migrate --no-interaction
vendor/bin/contao-console contao:symlinks        # veröffentlicht das Frontend-CSS
vendor/bin/contao-console cache:clear
```

## Grounding Page anlegen (Backend)

Unter **Inhalte → Grounding Pages** eine Entität anlegen:

- **Entität:** Name, Alias, Sprache, Entitätstyp, optionaler eigener schema.org-Typ.
- **Definition & Abgrenzung:** kanonische Definition, Segment, Klassifikation (geografischer
  Bereich, übergeordnete Entität mit URL, wichtige Beziehungen), „Nicht zu verwechseln mit …",
  externe Identitäts-URLs (`sameAs`, z. B. Wikidata/Wikipedia/LinkedIn), Entity-Identifier und
  Referenzseite (Anker).
- **Governance:** Herausgeber, Verantwortlich, Status, Version, Veröffentlichungsdatum, zuletzt
  aktualisiert, Änderungsprotokoll und ein Korrektur-/Kontaktpfad.
- **Menschliches Fakten-Review (Standard v1.7):** Reviewer-Typ (Person oder Organisation),
  Reviewer-Name und Datum des letzten menschlichen Reviews. Name und Datum gibt es nur zusammen, das
  Datum darf nicht in der Zukunft liegen. „Geprüft nach Standard“ (1.7 oder 2.0) legt fest, welche
  Version die Seite beansprucht (siehe Abschnitt Standard 2.0).

Darunter beliebig viele, per Drag & Drop sortierbare **Abschnitte**. Der gewählte Abschnittstyp
schaltet die passenden Eingabefelder frei:

| Abschnittstyp   | Zeilen-Felder                                       |
| --------------- | --------------------------------------------------- |
| Faktentabelle   | Bezeichnung · Wert                                  |
| Zeitleiste      | Jahr · Ereignis                                     |
| Begriffe        | Begriff · Definition                                |
| Häufige Fragen  | Frage · Antwort                                     |
| Quellen         | Titel · URL (+ Identifikatoren: Bezeichnung · Wert) |
| Weiterführende Literatur | Titel · URL                                |

Die Reihenfolge der Abschnitte ist frei (Standard v1.6.1: empfohlen, nicht verpflichtend).
„Weiterführende Literatur" gehört in den unteren Seitenbereich und darf vor oder nach FAQ und
Quellen stehen. Klassifikationsangaben erscheinen kompakt im Seitenkopf, nicht als eigener Abschnitt.

### Menschliches Review und Standardversion

Seit Standard v1.7 prüft und gibt ein Mensch die Fakten jeder Grounding Page vor der
Veröffentlichung frei. Eine Seite mit Reviewer **und** Review-Datum zeigt im Footer den Hinweis
„Fakten geprüft von … · Zuletzt von einem Menschen geprüft: …“, gibt `reviewedBy` und `lastReviewed`
an der `WebPage` aus und nennt Standard v1.7. Ohne Review bleibt die Seite eine gültige
v1.6.1-Seite, ohne Hinweis und ohne Platzhalter.

- Das Review-Datum nur nach einem dokumentierten menschlichen Review setzen, nie durch Import,
  Deploy oder automatische Prüfungen.
- Inhaltliche Änderungen an Fakten brauchen vor der Veröffentlichung ein neues Review. Tippfehler,
  Layout und Umformulierungen ohne neue Fakten zählen nicht dazu.
- Wird eine Organisation genannt, muss die prüfende Person intern nachvollziehbar bleiben (die
  Contao-Versionierung zeigt, wer das Review gespeichert hat).
- „Zuletzt aktualisiert“ (früher „Zuletzt geprüft“) ist das Datum der letzten inhaltlichen Änderung
  und wird als `dateModified` ausgegeben. Es ist kein Review-Datum und wird nie in eines umgewandelt.

**Hinweise zum Upgrade (Standard v1.7)**

- Projekte, die `grounding/_standard_footer.html.twig` oder `grounding/_page.html.twig`
  überschreiben, müssen `@Contao/grounding/_review_notice.html.twig` einbinden und
  `grounding_standard(grounding.isReviewed)` aufrufen. Sonst fehlt der Hinweis, und der Footer nennt
  v1.6.1, während das JSON-LD bereits das Review trägt.
- `GroundingStandard::VERSION` wurde entfernt. Stattdessen `GroundingStandard::BASE_VERSION`,
  `REVIEWED_VERSION` oder `versionFor(bool)` verwenden. Der Sprachschlüssel
  `grounding.detail.standardTextVerified` ist veraltet (Alias von `standardTextUpdated`).

Der Seitenkopf (Name, Definition, Abgrenzung, Governance) wird immer aus den Kopfdaten gerendert —
er ist kein eigener Abschnitt.

### Standard 2.0 (Referenzintegrität)

Standard 2.0 übernimmt v1.7 als Teil A (Seitenqualität) und ergänzt Teil B (Referenzintegrität,
RI-1 bis RI-7). Das Bundle entscheidet je Seite, welche Version sie nennt. **Es wird nichts
automatisch hochgestuft.**

| Standard | Bedingung                                                                                          |
| -------- | -------------------------------------------------------------------------------------------------- |
| 1.6.1    | Kein menschliches Review (Reviewer-Name und Datum fehlen).                                         |
| 1.7      | Review vorhanden, Feld „Geprüft nach Standard“ leer oder `1.7`. Auch Rückfall bei 2.0.             |
| 2.0      | Review vorhanden, „Geprüft nach Standard“ = `2.0` **und** alle Referenzprüfungen bestanden.        |

Bestehende Datensätze bleiben deshalb ohne Datenänderung 1.7 bzw. 1.6.1. Besteht eine 2.0-Seite zur
Laufzeit eine Prüfung nicht, wird sie auf 1.7 herabgestuft und eine Warnung ins Contao-Log
geschrieben (Datensatz und Verstöße). Die Seite behauptet also nie fälschlich 2.0. Auf 2.0-Seiten
ist der Hinweis für menschliche Leser ausgeblendet.

**Entity-Identifier und Referenzseite**

- **Entity-Identifier** (`entityId`): absolute, stabile URI der Entität, dient als `@id` der
  Hauptentität. Leer heißt automatisch: Anker-URL (bzw. Seiten-URL) plus Typ-Fragment
  (`#organization`, `#person`, `#softwareapplication`, bei `Thing` `#entity`). Internationalisierte
  URLs (Umlaute, IDN-Hosts) werden akzeptiert.
- **Referenzseite (Anker)** (`anchorUrl`): URL der Seite, die die Entität maßgeblich beschreibt, in
  der Sprache des Datensatzes. Leer heißt: diese Seite ist der Anker. `mainEntityOfPage` zeigt auf den
  Anker; Seiten, die nicht der Anker sind (`isAnchor` ist false), zeigen einen sichtbaren Link
  „Offizielle Informationen zu …“ dorthin. Entspricht `anchorUrl` der eigenen Seiten-URL, gilt die
  Seite als Anker.
- Der automatische Identifier ist nur stabil, solange ein Datensatz auf genau einer Seite in einer
  Sprache eingebunden ist. **Mehrsprachiger Fall:** in allen Sprachdatensätzen denselben `entityId`
  setzen (am besten die `x-default`-Anker-URL mit Fragment), `anchorUrl` je Sprache mit der Anker-URL
  der eigenen Sprache füllen und auf der Ankerseite selbst leer lassen.

**`sameAs`-Regeln (RI-3)** — nur offizielle Profile, Einträge in Identitätsregistern (Wikidata, ISNI,
ORCID, GND, LEI, Handelsregister) oder Nachschlagewerk-Einträge speziell zu dieser Entität. Eine URL
wird abgelehnt, wenn sie

1. keine absolute http(s)-URL ist,
2. auf die eigene Domain zeigt (Host von Seite, Identifier oder Anker sowie die Domains aller
   Startseiten; `www.` und Subdomains werden normalisiert),
3. eine Such-, Listen- oder API-URL ist (`/search`, `/api/`, `api.php`, `Special:Search` oder eine
   Query `q`, `s`, `query`, `search`),
4. auf eine Publikation zeigt (`doi.org`, `dx.doi.org`, `arxiv.org`),
5. ein Fragment hat (Abschnitt eines fremden Eintrags).

Bei einem Datensatz mit „Geprüft nach Standard 2.0“ **blockiert** ein Verstoß das Speichern, mit einer
Meldung je URL und Grund. Sonst (1.7 oder ohne Review) zeigt dieselbe Prüfung nur einen Hinweis. Ob ein
Profil wirklich offiziell ist, entscheidet weiterhin das Review; es gibt weder Allowlist noch
Erreichbarkeitsprüfung. Werte, die keine http(s)-URLs sind, werden aus dem JSON-LD und dem sichtbaren
Block entfernt, und eine geprüfte 2.0-Seite mit so einem Wert wird zur Laufzeit auf 1.7 herabgestuft.

**Offizielle Profile und Kennungen.** Jede `sameAs`-URL und jede Kennung der Hauptentität steht im
sichtbaren Block „Offizielle Profile und Kennungen von …“ direkt vor dem Standard-Footer (RI-4).
`sameAs`-URLs erscheinen als Links, Kennungen als Bezeichnung/Wert-Liste. Die Sektion „Quellen“
enthält nur noch Quellen (Standardüberschrift „Quellen zu …“), damit offizielle Profile von Berichten
Dritter unterscheidbar bleiben (RI-5). Im JSON-LD stehen Nicht-URL-Kennungen als `identifier`
(`PropertyValue` mit `propertyID` und `value`) statt in `additionalProperty`; das gilt für alle
Versionen. Standardüberschriften tragen jetzt den Entitätsnamen (P-3): „Abgrenzung von …“, „Quellen
zu …“, „Änderungen an …“.

**Upgrade 1.7 → 2.0 für Redaktionen**

1. `Entity-Identifier` prüfen und setzen, wenn der Datensatz auf mehreren Seiten oder in mehreren
   Sprachen genutzt wird.
2. Die `Referenzseite (Anker)` auf Nicht-Anker-Seiten und Sprachversionen festlegen.
3. `sameAs` gegen die obigen Regeln prüfen und eigene Seiten, Such-/API-URLs, DOI/arXiv und
   Abschnittslinks entfernen.
4. Das 7-Punkte-Review durchführen (die sechs Punkte aus v1.7 plus Referenzen) und dokumentieren, z. B.
   über Reviewer-Name und Datum.
5. „Geprüft nach Standard“ auf `2.0` stellen.

**Hinweise für Entwickler und Template-Overrides (Standard 2.0)**

- `vendor/bin/contao-console contao:migrate` ausführen (drei neue Spalten: `reviewStandard`,
  `entityId`, `anchorUrl`) und `vendor/bin/contao-console cache:clear`. Neue Abhängigkeiten:
  `ext-filter`, `psr/log`, `symfony/string`, `doctrine/dbal`, `symfony/routing`.
- Das Template erhält zwei neue Variablen: `identity` (Seiten-URL, `entityId`, `anchorUrl`,
  `isAnchor`) und `conformance` (genannte `version`, Verstöße).
- Überschriebene Footer rufen `grounding_standard(grounding.isReviewed, conformance.version)` auf. Mit
  nur `grounding.isReviewed` nennt ein Template höchstens 1.7, nie 2.0.
- Die Übersetzungsschlüssel `grounding.detail.distinction`, `sources` und `changelog` haben jetzt `%s`
  (Entitätsname). Neu: `officialProfiles`, `anchorLink`.
- Kennungen sind von `_sources.html.twig` nach `_official_profiles.html.twig` gewandert. Der Block ist
  eine `<section>` direkt im Seiten-Wrapper vor dem Standard-Footer. Überschriebene `_sources`-Templates
  funktionieren weiter (`section.identifiers` bleibt im DTO), sollten Kennungen aber nicht mehr
  ausgeben, sonst erscheinen sie doppelt.
- Das JSON-LD nutzt absolute `@id`s: WebPage-`@id` = Seiten-URL, `DefinedTerm` = `<Seite>#term-<slug>`,
  FAQ = `<Seite>#faq`; die übergeordnete Entität ist bei gesetzter URL ein eigener Knoten.

## In eine Seite einbetten (Frontend)

1. In einem beliebigen Artikel ein Inhaltselement vom Typ **„Grounding Page"** (Gruppe *Includes*)
   einfügen und den gewünschten Grounding-Datensatz auswählen.
2. Die Seite gibt die komplette Grounding Page im normalen Layout aus (Header, Human-Notice,
   Abschnitte, Standard-Footer). Das JSON-LD landet automatisch im `<head>`, ebenso `canonical`,
   Meta-Description und `hreflang` aus dem Contao-Layout.

Für eine **dedizierte Faktenseite** legst du einfach eine normale Contao-Seite an (z. B.
`/fakten/<name>`) und platzierst dort dieses eine Element.

## Mehrsprachigkeit

Über **separate Contao-Sprachbäume** (Contao-Standard): pro Sprache eine Seite mit dem Element, das
auf den sprachpassenden Datensatz zeigt (z. B. `cern` und `cern-en`). Das Sprachfeld des Datensatzes
bestimmt `inLanguage`; die `hreflang`-Verknüpfung liefert Contao über die Sprachbäume automatisch.

## JSON-LD (schema.org)

Pro Seite entsteht **ein** Graph mit einer `WebPage`, deren `mainEntity` die aufgelöste Haupt-Entität
ist (z. B. `Organization`, `Person`, `Product`). `sameAs`, Fakten (`additionalProperty`), `DefinedTerm`-
und `FAQPage`-Knoten werden aus den Abschnitten erzeugt. Quellen erscheinen als `citation`, weiterführende
Literatur als `relatedLink` an der `WebPage`. Eine geprüfte Seite trägt zusätzlich
`reviewedBy` (`Person` oder `Organization`) und `lastReviewed`. `datePublished` spiegelt das
Veröffentlichungsdatum neben `dateCreated`. Die Klassifikation wird typgerecht gespiegelt
(`areaServed`/`spatialCoverage`, `parentOrganization`/`isPartOf`), sonst als `PropertyValue`. Segment
und Beziehungen stehen als `PropertyValue` in `additionalProperty`. Die Ausgabe erfolgt über Contaos
ResponseContext, sodass das `<script type="application/ld+json">` im `<head>` steht — auch wenn das
Element mitten im Seiteninhalt platziert ist. Prüfbar mit dem Schema.org Validiator.

## Erweiterbarkeit

- **Beliebiger `@type` ad hoc:** das Kopf-Feld *Eigener schema.org-Typ* füllen — es hat Vorrang vor
  der Standard-Zuordnung, und der passende schema.org-Knoten wird erzeugt (sonst `Thing`).
- **Neue Ontologie-Klasse:** einen Fall in `src/Enum/GroundingEntityType.php` samt Zuordnung zu einem
  schema.org-Typ ergänzen — er steht danach automatisch im Backend zur Auswahl und im JSON-LD.

## Gestaltung

Das mitgelieferte CSS (`public/grounding.css`) nutzt Bootstrap-CSS-Variablen (`--bs-*`) mit
neutralen Fallback-Werten und übernimmt daher automatisch die Farben deines Themes. Alle Klassen
sind mit `grounding-page` präfixiert und lassen sich im Theme überschreiben. Die Twig-Templates
liegen unter `contao/templates/grounding/` und können projektspezifisch überschrieben werden.

## Barrierefreiheit (WCAG 2.x AA / BFSG)

Semantisches Markup (`article`/`section`/`dl`/`ol`/`time`, saubere Überschriften-Hierarchie,
`aside role="note"` für den Hinweis-Kasten), themekonforme Kontraste und ein responsives Layout.

## Entwicklung

```bash
composer all   # Rector (dry-run) → ECS → PHPStan → depcheck → PHPUnit
```

Einzeln: `composer ecs` (Auto-Fix), `composer phpstan`, `composer rector`, `composer depcheck`,
`composer tests`.

## Entstehung

Dieses Bundle wurde mit KI-Unterstützung (Claude von Anthropic) geplant und erstellt:
Spezifikation, Implementierung, Tests und Dokumentation entstanden in Zusammenarbeit mit Claude
und wurden von einem Menschen geprüft.

## Lizenz

[LGPL-3.0-or-later](https://www.gnu.org/licenses/lgpl-3.0.html).
