# Contao Grounding Pages (`trassd/contao-grounding-pages`)

*Deutsche Version: [README_DE.md](README_DE.md).*

Integrates **Grounding Pages** according to the *Grounding Page Standard v2.0*
(<https://groundingpage.com/>) into Contao. A Grounding Page is a factual, clearly structured
knowledge entry per entity, written for **humans and AI/answer engines** (GEO/AEO). The bundle
provides a back end data model, a front end element and **exactly one** standard-compliant JSON-LD
document per page.

Background and walkthrough (German blog post):
[Grounding Pages in Contao](https://vanhulst.de/blog/grounding-pages-contao).

## Concept

- **One record = one entity.** A parent table holding the entity's governance data and a sortable
  child table holding the content sections.
- **Embedded into the page.** The complete Grounding Page is placed as a **content element** inside
  an article and renders within the normal page layout (header, footer, theme).
- **Full ontology** (18 entity classes mapped to schema.org, fallback `Thing`), extensible via
  a free-text field for arbitrary `@type` values.

## Requirements

- PHP `^8.3`, Contao `^5.7`
- `spatie/schema-org` (`^3.10 || ^4.0`), shipped with the Contao core

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
- **Definition & distinction:** canonical definition, segment, classification (geographic scope,
  parent entity with URL, key relationships), “not to be confused with …”, external identity URLs
  (`sameAs`, e.g. Wikidata/Wikipedia/LinkedIn), entity identifier and reference page (anchor).
- **Governance:** publisher, maintainer, status, version, publication date, last updated, a
  changelog and a correction/contact path.
- **Human fact review:** reviewer type (person or organization), reviewer name and
  date of the last human review. Name and date must be entered together, the date must not be in
  the future. “Reviewed against standard” (1.7 or 2.0) sets the version the page claims (see the
  Standard 2.0 section). Without reviewer name and date, Contao refuses to save.

Below that, add any number of drag-and-drop sortable **sections**. The chosen section type reveals
the matching input fields:

| Section type    | Row fields                                    |
| --------------- | --------------------------------------------- |
| Fact grid       | Label · Value                                 |
| Timeline        | Year · Event                                  |
| Defined terms   | Term · Definition                             |
| FAQ             | Question · Answer                             |
| Sources         | Title · URL (+ identifiers: Label · Value)    |
| Further reading | Title · URL                                   |

Section order is free (Standard v1.6.1 only recommends one). “Further reading” belongs in the lower
part of the page and may come before or after the FAQ and sources. Classification metadata sits
compactly in the page header.

The bundle renders the page header (name, definition, distinction, governance) from the record's
main data.

### Human review and standard version

Since Standard v1.7, a person must review and approve the facts of every Grounding Page before
publication. A page with reviewer **and** review date shows the notice “Facts reviewed by … · Last
reviewed by a human: …” in the footer, emits `reviewedBy` and `lastReviewed` on the `WebPage`, and
declares Standard v1.7. Without a review the page stays a valid v1.6.1 page: no notice, no
placeholder.

- Set the review date only after a documented human review, never through imports, deploys or
  automated checks.
- A substantive change to facts requires a new review before publication. Typos, layout and
  rewording without new facts do not.
- If you name an organization, the reviewing person must remain traceable internally (Contao's
  record versioning shows who saved the review).
- “Last updated” (formerly “Last verified”) is the date of the last content change and is emitted as
  `dateModified`. The bundle never uses it as a review date.

**Upgrade notes (Standard v1.7)**

- Projects that override `grounding/_standard_footer.html.twig` or `grounding/_page.html.twig` must
  include `@Contao/grounding/_review_notice.html.twig` and call
  `grounding_standard(grounding.isReviewed)`. Otherwise the notice is missing and the footer shows
  v1.6.1 while the JSON-LD already carries the review.
- `GroundingStandard::VERSION` was removed. Use `GroundingStandard::BASE_VERSION`,
  `REVIEWED_VERSION` or `versionFor(bool)`. The language key
  `grounding.detail.standardTextVerified` is deprecated (alias of `standardTextUpdated`).

### Standard 2.0 (reference integrity)

Standard 2.0 adopts v1.7 as part A (page quality) and adds part B (reference integrity, RI-1 to
RI-7). The bundle decides per page which version it states and **never upgrades a page automatically.**

| Standard | Condition                                                                                     |
| -------- | --------------------------------------------------------------------------------------------- |
| 1.6.1    | No human review (reviewer name and date missing).                                             |
| 1.7      | Human review, field “Reviewed against standard” empty or `1.7`. Also the fallback for 2.0.    |
| 2.0      | Human review, “Reviewed against standard” = `2.0` **and** all reference checks pass.          |

Existing records therefore stay v1.7 or v1.6.1 without any data change. If a reviewed 2.0 page fails
a check at render time, the bundle states 1.7 instead and writes a warning to the Contao log (entity
name, page URL and violations; at most once a day per page and set of violations, via the
`cache.app` pool). On 2.0 pages the bundle hides the hint for human readers.

**Entity identifier and reference page**

- **Entity identifier** (`entityId`): absolute, stable URI of the entity, used as `@id` of the main
  entity. Empty means automatic: anchor URL (or page URL) plus a type fragment (`#organization`,
  `#person`, `#softwareapplication`, `#entity` for `Thing`). URLs with umlauts and IDN hosts are
  allowed.
- **Reference page (anchor)** (`anchorUrl`): URL of the page that authoritatively describes the entity,
  in the language of the record. Empty means this page is the anchor. `mainEntityOfPage` points to the
  anchor; pages that are not the anchor (`isAnchor` is false) show a visible link “Official information
  on …” to it. A page whose `anchorUrl` equals its own URL counts as the anchor.
- The automatic identifier is stable only while a record is embedded on exactly one page in one
  language. **Multilingual case:** use the same `entityId` in every language record (ideally the
  `x-default` anchor URL with a fragment), set `anchorUrl` per language to that language's anchor URL,
  and leave it empty on the anchor page itself.
- **Your own organisation (site publisher):** if the record describes the publisher of the site and
  `trassd/contao-schema-essentials` is installed, set `entityId` to its publisher id
  (`<root page base URL>#organization`, or `#person` for a person publisher). The same entity then has
  one `@id` site-wide.
- The bundle ignores stored values that are not absolute http(s) URLs (e.g. written by import or SQL
  past the save check) at render time and uses the automatic value. A reviewed 2.0 page then states 1.7.

**`sameAs` rules (RI-3).** Allowed are official profiles, identity registers (Wikidata, ISNI, ORCID, GND,
LEI, commercial register) or reference entries specifically about this entity. The bundle rejects a URL if it:

1. is not an absolute http(s) URL,
2. points to your own domain (`www.` and subdomains count as yours). Own hosts on save: the hosts of
   `entityId` and `anchorUrl` plus the domains (DNS) of all root pages. At render time: the hosts of
   the page URL, the entity identifier and the anchor,
3. is a search, list or API URL (`/search`, `/api/`, `api.php`, `Special:Search`, or a query `q`, `s`,
   `query`, `search`),
4. points to a publication (`doi.org`, `dx.doi.org`, `arxiv.org`),
5. has a fragment (section of someone else's entry).

On a record with “Reviewed against standard 2.0”, a violation **blocks saving** with one message per
URL and reason. With 1.7 or no review, Contao shows the same text as a notice and saves. The reviewer decides
whether a profile is official; the bundle has no allowlist and does not check reachability. The
bundle drops values that are not http(s) URLs from the JSON-LD and the visible block, and a reviewed
2.0 page with such a value states 1.7 at render time.

**Official profiles and identifiers.** The bundle shows every `sameAs` URL and every identifier of the
main entity in the visible block “Official profiles and identifiers of …” before the standard footer
(RI-4). `sameAs` URLs appear as links, identifiers as a label/value list. The “Sources”
section contains only sources (default heading “Sources about …”), so readers can tell official
profiles from third-party reports (RI-5). In JSON-LD, non-URL identifiers appear as
`identifier` (`PropertyValue` with `propertyID` and `value`) instead of `additionalProperty`; this
applies to all versions. Standard headings carry the entity name (P-3): “Distinguishing …”,
“Sources about …”, “Changes to …”, “Defined Terms for …”, “Frequently Asked Questions about …”.

**Upgrade 1.7 → 2.0 for editors**

1. Check `Entity identifier`, set it if the record is used on several pages or languages.
2. Set the `Reference page (anchor)` on non-anchor pages and language versions.
3. Check `sameAs` against the rules above and remove own pages, search/API URLs, DOI/arXiv and
   section links.
4. Perform the 7-point review (the 6 v1.7 points plus references) and document it, e.g. through the
   reviewer name and date.
5. Set “Reviewed against standard” to `2.0`.

**Upgrade notes for developers and template overrides (Standard 2.0)**

- Run `vendor/bin/contao-console contao:migrate` (three new columns: `reviewStandard`, `entityId`,
  `anchorUrl`) and `vendor/bin/contao-console cache:clear`. New requirements: `ext-filter`,
  `psr/log`, `symfony/string`, `doctrine/dbal`, `symfony/routing`, `symfony/cache-contracts`.
- The template receives two new variables: `identity` (page URL, `entityId`, `anchorUrl`, `isAnchor`)
  and `conformance` (stated `version`, violations).
- Overridden footers should call `grounding_standard(grounding.isReviewed, conformance.version)`.
  With only `grounding.isReviewed` a template states at most 1.7, never 2.0.
- The bundle templates use new translation keys with `%s` (the entity name): `distinctionNamed`,
  `sourcesNamed`, `changelogNamed`, `definedTermsNamed`, `faqNamed`, `officialProfiles` and
  `anchorLink` (all under `grounding.detail`). The old keys `distinction`, `sources`, `changelog`,
  `definedTerms` and `faq` are unchanged, so overrides calling them with `trans([])` keep working.
- Identifiers moved from `_sources.html.twig` to `_official_profiles.html.twig`. The block is a
  `<section>` child of the page wrapper, placed before the standard footer. Overridden `_sources`
  templates keep working (`section.identifiers` remains in the DTO) but should no longer print
  identifiers, otherwise they appear twice.
- The JSON-LD uses absolute `@id`s: WebPage `@id` = page URL, `DefinedTerm` = `<page>#term-<slug>`,
  FAQ = `<page>#faq`, and the parent entity is its own node when a parent URL is set.

## Embedding into a page (front end)

1. In any article, insert a content element of type **“Grounding Page”** (group *Includes*) and
   select the desired Grounding record.
2. The page outputs the complete Grounding Page within the normal layout (header, hint for human
   readers up to Standard 1.7, sections, profiles block, standard footer). The JSON-LD goes into the
   `<head>`, as do `canonical`, the meta description and `hreflang` from the Contao layout.

For a **dedicated facts page**, create a normal Contao page (e.g. `/facts/<name>`) and place
this single element on it.

## Multilingual

Via **separate Contao language trees** (the Contao standard): one page per language holding the
element that points to the matching-language record (e.g. `cern` and `cern-en`). The record's
language field determines `inLanguage`; Contao adds the `hreflang` links through the language
trees.

## JSON-LD (schema.org)

Each page produces **one** graph containing a `WebPage` whose `mainEntity` is the resolved main
entity (e.g. `Organization`, `Person`, `Product`). From the sections the bundle builds `sameAs`,
facts (`additionalProperty`), `identifier`, and `DefinedTerm` and `FAQPage` nodes. Sources appear as `citation` and
further reading as `relatedLink` on the `WebPage`. A reviewed page also carries `reviewedBy`
(`Person` or `Organization`) and `lastReviewed`. `datePublished` mirrors the publication date next
to `dateCreated`. Classification is mirrored with type-appropriate properties
(`areaServed`/`spatialCoverage`, `parentOrganization`/`isPartOf`), otherwise as `PropertyValue`.
Segment and relationships are `PropertyValue` entries in `additionalProperty`. The bundle writes through
Contao's response context, so the `<script type="application/ld+json">` ends up in the `<head>`, even
when the element sits in the middle of the page content. Check the output with the
[Schema.org validator](https://validator.schema.org/).

## Extensibility

- **Arbitrary `@type` ad hoc:** fill the *Custom schema.org type* field. It takes precedence over
  the standard mapping, and the bundle builds the matching schema.org node (otherwise `Thing`).
- **New ontology class:** add a case to `src/Enum/GroundingEntityType.php` with its mapping to a
  schema.org type. The back end select and the JSON-LD pick it up without further changes.

## Styling

The bundled CSS (`public/grounding.css`) uses Bootstrap CSS variables (`--bs-*`) with neutral
fallback values and so adopts your theme's colours. All classes carry the prefix `grounding-page`;
override them in your theme. The Twig templates live under `contao/templates/grounding/`; override
them per project.

## Accessibility (WCAG 2.x AA / BFSG)

Semantic markup (`article`/`section`/`dl`/`ol`/`time`, a clean heading hierarchy, `aside
role="note"` for the notice box), theme-consistent contrasts and a responsive layout.

## Development

```bash
composer all   # Rector (dry-run) → ECS → PHPStan → depcheck → PHPUnit
```

Individually: `composer ecs` (auto-fix), `composer phpstan`, `composer rector`, `composer depcheck`,
`composer tests`.

## How this bundle was made

Andreas van Hulst planned and built this bundle with AI assistance (Claude by Anthropic). The
specification, implementation, tests and documentation came out of a collaboration with Claude;
a human reviewed them.

## License

[LGPL-3.0-or-later](https://www.gnu.org/licenses/lgpl-3.0.html).
