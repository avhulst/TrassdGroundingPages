<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['tl_grounding_page']['title_legend'] = 'Entity';
$GLOBALS['TL_LANG']['tl_grounding_page']['definition_legend'] = 'Definition & distinction';
$GLOBALS['TL_LANG']['tl_grounding_page']['governance_legend'] = 'Governance';
$GLOBALS['TL_LANG']['tl_grounding_page']['publish_legend'] = 'Publication';

$GLOBALS['TL_LANG']['tl_grounding_page']['title'] = ['Name / title', 'Visible name of the entity and internal title.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['alias'] = ['Alias', 'URL-friendly, unique identifier.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['language'] = ['Language', 'Language code of this record (e.g. "de", "en") — used as inLanguage in the JSON-LD.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType'] = ['Entity type', 'schema.org class of the entity (fallback: Thing).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['customSchemaType'] = ['Custom schema.org type', 'Overrides the entity type mapping (optional, for any @type).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['definition'] = ['Canonical definition', 'Short, factual definition of the entity (not promotional).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['segment'] = ['Segment', 'Segment or category assignment of the entity.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['geographicScope'] = ['Geographic scope', 'Spatial scope of activity or validity (e.g. "International", "DACH", "Hamburg").'];
$GLOBALS['TL_LANG']['tl_grounding_page']['parentEntity'] = ['Parent entity', 'Name of the parent organization or entity (optional).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['parentEntityUrl'] = ['Parent entity URL', 'Official URL or grounding page of the parent entity (optional).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['relationships'] = ['Key relationships', 'Rows of relation, name and optional URL (e.g. "Member of · EIROforum").'];
$GLOBALS['TL_LANG']['tl_grounding_page']['distinction'] = ['Distinction', 'What the entity must not be confused with.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['sameAs'] = ['sameAs references', 'External identity URLs (Wikidata, Wikipedia, ROR, official website).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['publisher'] = ['Publisher', 'Organisation responsible for the definition.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['maintainer'] = ['Maintainer', 'Party maintaining the entry.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['status'] = ['Status', 'Editorial status (e.g. "Active Definition", "Draft").'];
$GLOBALS['TL_LANG']['tl_grounding_page']['entryVersion'] = ['Version', 'Version of the entry (e.g. "1.0").'];
$GLOBALS['TL_LANG']['tl_grounding_page']['datePublished'] = ['Published on', 'Date of first publication.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['dateVerified'] = ['Last verified', 'Date of the last review — emitted as dateModified.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['published'] = ['Publish', 'Make the grounding page visible in the front end.'];
$GLOBALS['TL_LANG']['tl_grounding_page']['changelog'] = ['Changelog', 'Rows of date and change (governance, shown in the footer).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['correctionContact'] = ['Correction / contact path', 'E-mail or URL for correction notices (shown in the footer).'];
$GLOBALS['TL_LANG']['tl_grounding_page']['col_changeDate'] = 'Date';
$GLOBALS['TL_LANG']['tl_grounding_page']['col_change'] = 'Change';
$GLOBALS['TL_LANG']['tl_grounding_page']['col_relation'] = 'Relation';
$GLOBALS['TL_LANG']['tl_grounding_page']['col_relationName'] = 'Name';
$GLOBALS['TL_LANG']['tl_grounding_page']['col_relationUrl'] = 'URL';

$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['organization'] = 'Organisation';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['person'] = 'Person';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['group-or-role'] = 'Group or role';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['product'] = 'Product';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['service'] = 'Service';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['tool-or-platform'] = 'Tool or platform';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['feature'] = 'Feature';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['segment'] = 'Segment';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['field-of-knowledge'] = 'Field of knowledge';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['concept'] = 'Concept';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['publication'] = 'Publication';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['dataset'] = 'Dataset';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['standard'] = 'Standard';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['method'] = 'Method';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['place'] = 'Place';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['event'] = 'Event';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['metric'] = 'Metric';
$GLOBALS['TL_LANG']['tl_grounding_page']['entityType']['project'] = 'Project';
