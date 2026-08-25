# Genealogy Dna

This independent Liberu module owns the provider-neutral **Genealogy Dna** capability.

It exposes a stable capability descriptor and service provider. Domain persistence, authorization, tenancy, jobs, and presentation adapters remain behind this package's public boundary; the matching API, Filament, and Livewire packages are optional adapters and never become core dependencies.

The domain includes provider-neutral autosomal segment matching and relationship estimation. It
accepts normalized chromosome/position genotype maps, applies mismatch tolerance and minimum
segment thresholds, and returns shared cM, segment details, confidence, and relationship labels.

- Composer package: `liberusoftware/module-genealogy-dna`
- Module installer name: `genealogy-dna`
- Category: capability
- PHP/Laravel: PHP 8.5 / Laravel 13

The package is designed for the Liberu Composer installer and must not depend on an application's `App\\` classes.
