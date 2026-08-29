# Genealogy People

This independent Liberu module owns the provider-neutral **Genealogy People** capability.

It exposes a stable capability descriptor and service provider. Domain persistence, authorization, tenancy, jobs, and presentation adapters remain behind this package's public boundary; the matching API, Filament, and Livewire packages are optional adapters and never become core dependencies.

## Merge recovery

Accepting a merge candidate invokes the transactional `MergePersons` action. It conservatively
fills missing primary fields, deduplicates and transfers names, identities, and life events,
retains the accepted candidate as a review record, and soft-deletes the duplicate with a
`merged_into` tombstone. The `PersonMerged` event is dispatched after commit for relationship,
evidence, and other module adapters to reconcile their references.

- Composer package: `liberusoftware/module-genealogy-people`
- Module installer name: `genealogy-people`
- Category: capability
- PHP/Laravel: PHP 8.5 / Laravel 13

The package is designed for the Liberu Composer installer and must not depend on an application's `App\\` classes.
