# Contao Grounding Pages (`trassd/contao-grounding-pages`)

*Deutsche Version: [README_DE.md](README_DE.md).*

Integrates **Grounding Pages** according to the *Grounding Page Standard v1.6*
(<https://groundingpage.com/>) into Contao. A Grounding Page is a factual, clearly structured
knowledge entry per entity — written for **humans and AI/answer engines** (GEO/AEO). The bundle
provides a back end data model, a front end element and **exactly one** standard-compliant JSON-LD
document per page.

## Concept

- **One record = one entity.** A parent table holding the entity's governance data and a sortable
  child table holding the content sections.
- **Embedded into the page.** The complete Grounding Page is placed as a **content element** inside
  an article and renders within the normal page layout (header, footer, theme).
- **Full v1.6 ontology** (18 entity classes mapped to schema.org, fallback `Thing`), extensible via
  a free-text field for arbitrary `@type` values.

## Requirements

- PHP `^8.3`, Contao `^5.7`
- `spatie/schema-org` (`^3.10 || ^4.0`) — provided by the Contao core

## Installation

```bash
composer require trassd/contao-grounding-pages
vendor/bin/contao-console contao:migrate --no-interaction
vendor/bin/contao-console contao:symlinks        # publishes the front end CSS
vendor/bin/contao-console cache:clear
```

## Creating a Grounding Page (back end)

Under **Content → Grounding Pages**, create an entity:

- **Entity:** name, alias, language, entity type, optional custom schema.org type.
- **Definition & distinction:** canonical definition, segment, “not to be confused with …”,
  external identity URLs (`sameAs`, e.g. Wikidata/Wikipedia/LinkedIn).
- **Governance:** publisher, maintainer, status, version, publication date, last verified, a
  changelog and a correction/contact path.

Below that, add any number of drag-and-drop sortable **sections**. The chosen section type reveals
the matching input fields:

| Section type    | Row fields                                    |
| --------------- | --------------------------------------------- |
| Fact grid       | Label · Value                                 |
| Timeline        | Year · Event                                  |
| Defined terms   | Term · Definition                             |
| FAQ             | Question · Answer                             |
| Sources         | Title · URL (+ identifiers: Label · Value)    |

The page header (name, definition, distinction, governance) is always rendered from the entity's
main data — it is not a separate section.

## Embedding into a page (front end)

1. In any article, insert a content element of type **“Grounding Page”** (group *Includes*) and
   select the desired Grounding record.
2. The page outputs the complete Grounding Page within the normal layout (header, human notice,
   sections, standard footer). The JSON-LD is placed automatically in the `<head>`, as are
   `canonical`, the meta description and `hreflang` from the Contao layout.

For a **dedicated facts page**, simply create a normal Contao page (e.g. `/facts/<name>`) and place
this single element on it.

## Multilingual

Via **separate Contao language trees** (the Contao standard): one page per language holding the
element that points to the matching-language record (e.g. `cern` and `cern-en`). The record's
language field determines `inLanguage`; the `hreflang` links are provided by Contao through the
language trees automatically.

## JSON-LD (schema.org)

Each page produces **one** graph containing a `WebPage` whose `mainEntity` is the resolved main
entity (e.g. `Organization`, `Person`, `Product`). `sameAs`, facts (`additionalProperty`),
`DefinedTerm` and `FAQPage` nodes are generated from the sections. Output runs through Contao's
response context, so the `<script type="application/ld+json">` ends up in the `<head>` — even when
the element sits in the middle of the page content. Verifiable with the Schema.org validator.

## Extensibility

- **Arbitrary `@type` ad hoc:** fill the *Custom schema.org type* field — it takes precedence over
  the standard mapping and the matching schema.org node is generated (otherwise `Thing`).
- **New ontology class:** add a case to `src/Enum/GroundingEntityType.php` with its mapping to a
  schema.org type — it then automatically appears in the back end select and in the JSON-LD.

## Styling

The bundled CSS (`public/grounding.css`) uses Bootstrap CSS variables (`--bs-*`) with neutral
fallback values and therefore adopts your theme's colours automatically. All classes are prefixed
with `grounding-page` and can be overridden in the theme. The Twig templates live under
`contao/templates/grounding/` and can be overridden per project.

## Accessibility (WCAG 2.x AA / BFSG)

Semantic markup (`article`/`section`/`dl`/`ol`/`time`, a clean heading hierarchy, `aside
role="note"` for the notice box), theme-consistent contrasts and a responsive layout.

## Development

```bash
composer all   # Rector (dry-run) → ECS → PHPStan → depcheck → PHPUnit
```

Individually: `composer ecs` (auto-fix), `composer phpstan`, `composer rector`, `composer depcheck`,
`composer tests`.

## License

[LGPL-3.0-or-later](https://www.gnu.org/licenses/lgpl-3.0.html).
