# Contao Grounding Pages (`trassd/contao-grounding-pages`)

Integriert **Grounding Pages** nach dem *Grounding Page Standard v1.6*
(<https://groundingpage.com/>) in Contao. Eine Grounding Page ist ein faktischer, klar
strukturierter Wissenseintrag pro Entität — geschrieben für **Menschen und KI-/Antwortmaschinen**
(GEO/AEO). Das Bundle liefert dafür ein Backend-Modell, ein Frontend-Element und **genau ein**
standardkonformes JSON-LD-Dokument pro Seite.

## Konzept

- **Ein Datensatz = eine Entität.** Eine Kopftabelle mit den Governance-Daten der Entität und eine
  sortierbare Kindtabelle mit den inhaltlichen Abschnitten.
- **Eingebettet in die Seite.** Die komplette Grounding Page wird als **Content-Element** in einem
  Artikel platziert und rendert im normalen Seiten-Layout (Header, Footer, Theme).
- **Volle v1.6-Ontologie** (18 Entitätsklassen, abgebildet auf schema.org, Fallback `Thing`),
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
- **Definition & Abgrenzung:** kanonische Definition, Segment, „Nicht zu verwechseln mit …",
  externe Identitäts-URLs (`sameAs`, z. B. Wikidata/Wikipedia/LinkedIn).
- **Governance:** Herausgeber, Verantwortlich, Status, Version, Veröffentlichungsdatum, zuletzt
  geprüft, Änderungsprotokoll und ein Korrektur-/Kontaktpfad.

Darunter beliebig viele, per Drag & Drop sortierbare **Abschnitte**. Der gewählte Abschnittstyp
schaltet die passenden Eingabefelder frei:

| Abschnittstyp   | Zeilen-Felder                                       |
| --------------- | --------------------------------------------------- |
| Faktentabelle   | Bezeichnung · Wert                                  |
| Zeitleiste      | Jahr · Ereignis                                     |
| Begriffe        | Begriff · Definition                                |
| Häufige Fragen  | Frage · Antwort                                     |
| Quellen         | Titel · URL (+ Identifikatoren: Bezeichnung · Wert) |

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
und `FAQPage`-Knoten werden aus den Abschnitten erzeugt. Die Ausgabe erfolgt über Contaos
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

## Lizenz

[LGPL-3.0-or-later](https://www.gnu.org/licenses/lgpl-3.0.html).
