# Genealogy Architecture

## Overview

The genealogy domain models family history research: persons, families, relationships, sources, and evidence. It supports multiple visualization formats, DNA integration, collaborative research projects, and GEDCOM/Gramps XML data exchange with privacy protection for living individuals.

## Person and Family Records

- `Person` records store core identity data: names (with variants), gender, living/deceased status, and custom attributes.
- `Family` records link spouses and children with relationship types (biological, adoptive, foster, step).
- Life events (birth, marriage, death, burial, occupation) attach to persons with date ranges, place references, and notes.
- Attributes capture physical descriptions, education, religion, military service, and custom user-defined fields.
- Name variants support nicknames, maiden names, patronymics, and alternate spellings.

## Tree Visualization

- Pedigree charts render an ancestor view configurable by generation depth (2 through 10+).
- Descendant charts display children, grandchildren, and further descendants with collapsible branches.
- Fan charts render a circular radial layout with color-coded generations.
- Timeline views plot life events chronologically for a single person or family unit.
- All visualizations render server-side as SVG with client-side interactivity for zoom and pan.

## Source and Citation Management

- `Source` records describe repositories (books, archives, websites, interviews) with full bibliographic metadata.
- Source types include primary, secondary, and tertiary with reliability ratings (proven, probable, questionable).
- Citations link specific facts to source pages with transcription text, notes, and confidence levels.
- Transcriptions store full text excerpts with line-by-line references for verification.
- Source templates adapt fields based on source type (census, birth certificate, military record).

## Evidence Linking

- Each claim (fact, event, relationship) accumulates evidence through linked citations.
- Confidence levels (proven, probable, disputed, disproven) reflect evidence strength.
- Conflicting evidence is preserved rather than overwritten, with resolution workflows for researchers.
- Resolution actions: accept one source, mark conflict for further research, or add explanatory notes.
- Change tracking records who modified evidence links and when.

## Place Hierarchy

- Places form hierarchical structures: continent, country, county, state, city, cemetery, address.
- Historical names track place name changes over time with effective date ranges.
- Geocoding resolves places to coordinates via pluggable providers (Google, OpenStreetMap, GeoNames).
- Jurisdiction boundaries define administrative regions for historical research context.
- Place pages aggregate all persons and events associated with a location.

## GEDCOM and Gramps XML Import/Export

- GEDCOM import maps standard tags (INDI, FAM, NAME, BIRT, DEAT, MARR, SOUR, NOTE) to internal models.
- Gramps XML import handles extended attributes, media objects, and repository references.
- Export generates GEDCOM 5.5.1 or 7.0 with configurable privacy filtering.
- Import wizards preview detected records before committing, with conflict resolution options.
- Repeated imports are idempotent, updating existing records rather than duplicating.

## DNA Integration

- `DnaKit` records store test type (autosomal, Y-DNA, mtDNA), testing company, and raw data references.
- `DnaMatch` records track matches with shared centimorgan ranges and segment data.
- Triangulation groups identify segments shared across three or more matches indicating common ancestry.
- Chromosome browsers visualize match segments across chromosomal positions.
- Integration APIs fetch match data from testing providers (GEDmatch, FamilyTreeDNA, 23andMe).

## Privacy Controls

- Living persons are detected through birth date recency and configurable thresholds.
- Living person data is redacted from public views, APIs, and exports based on access level.
- Access levels: public ( deceased only), registered (deceased + living details hidden), contributor (full), owner (full + edit).
- Tree-level privacy settings override individual person settings for consistency.
- Export and API responses enforce privacy filtering automatically.

## Research Projects and Collaboration

- Research projects group related tasks, notes, and findings around a research question.
- Collaboration supports shared trees with per-user access grants and contribution tracking.
- Discussion threads attach to persons, families, sources, or projects for research collaboration.
- Research logs track queries, negative findings, and follow-up tasks to prevent duplicated effort.
- Citation sharing allows collaborators to attach project sources to shared person records.

## Module Boundaries and Extension Points

| Module | Responsibility | Extension Points |
|--------|---------------|------------------|
| Records | Persons, families, events | Custom event types, attributes |
| Visualization | Charts, timelines | Custom chart renderers |
| Sources | Citations, transcriptions | Custom source templates |
| Evidence | Claims, conflicts, resolution | Custom confidence algorithms |
| Places | Hierarchy, geocoding | Custom geocoding providers |
| Exchange | GEDCOM, Gramps XML | Custom import/export formats |
| DNA | Kits, matches, triangulation | Custom testing provider APIs |
| Privacy | Access control, redaction | Custom access level policies |
| Research | Projects, collaboration | Custom research workflows |

### Extension Points

- Register custom chart renderers via the `ChartRendererRegistry`.
- Implement `GeocoderProviderInterface` to add place lookup services.
- Add DNA provider integrations through the `DnaProviderInterface`.
- Hook into import/export pipelines with event listeners on `GenealogyImported` and `GenealogyExported`.
- Define custom event and attribute types through configuration.
- Register privacy policy enforcers for custom access level logic.
