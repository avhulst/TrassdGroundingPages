# Contao Grounding Pages (`trassd/contao-grounding-pages`)

Integriert **Grounding Pages** nach dem *Grounding Page Standard v1.7*
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
  externe Identitäts-URLs (`sameAs`, z. B. Wikidata/Wikipedia/LinkedIn).
- **Governance:** Herausgeber, Verantwortlich, Status, Version, Veröffentlichungsdatum, zuletzt
  aktualisiert, Änderungsprotokoll und ein Korrektur-/Kontaktpfad.
- **Menschliches Fakten-Review (Standard v1.7):** Reviewer-Typ (Person oder Organisation),
  Reviewer-Name und Datum des letzten menschlichen Reviews. Name und Datum gibt es nur zusammen, das
  Datum darf nicht in der Zukunft liegen.

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

Der Seitenkopf (Name, Definition, Abgrenzung, Governance) wird immer aus den Kopfdaten gerendert —
er ist kein eigener Abschnitt.

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
Literatur als `relatedLink` an der `WebPage`. Eine geprüfte Seite trägt zusätzlich `reviewedBy` (`Person` oder `Organization`) und `lastReviewed`. `datePublished` spiegelt das Veröffentlichungsdatum neben `dateCreated`. Die Klassifikation wird typgerecht gespiegelt
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
