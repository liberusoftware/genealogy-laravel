# Genealogy Dna

This independent Liberu module owns the provider-neutral **Genealogy Dna** capability.

It exposes a stable capability descriptor and service provider. Domain persistence, authorization, tenancy, jobs, and presentation adapters remain behind this package's public boundary; the matching API, Filament, and Livewire packages are optional adapters and never become core dependencies.

The domain includes provider-neutral autosomal segment matching and relationship estimation. It
accepts normalized chromosome/position genotype maps, applies mismatch tolerance and minimum
segment thresholds, and returns shared cM, segment details, confidence, and relationship labels.
It also triangulates three or more match segment sets to identify shared groups above a configured
cM threshold.
Raw DNA content can be validated in memory for supported 23andMe, Ancestry, MyHeritage, FTDNA,
and generic rsID formats before any persistence workflow is selected.
Provider records are tenant-scoped and can be associated with kits while retaining the
legacy provider label. `ImportDnaKit` validates raw content, encrypts it through
`DnaFileVault` on the private disk, records a SHA-256 hash/format/SNP count, and removes
the vault entry when a kit is deleted. Legacy plaintext files remain readable for migration.

- Composer package: `liberusoftware/module-genealogy-dna`
- Module installer name: `genealogy-dna`
- Category: capability
- PHP/Laravel: PHP 8.5 / Laravel 13

The package is designed for the Liberu Composer installer and must not depend on an application's `App\\` classes.
