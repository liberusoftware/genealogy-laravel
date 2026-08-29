# Genealogy Relationships

This independent Liberu module owns the provider-neutral **Genealogy Relationships** capability.

It exposes a stable capability descriptor and service provider. Domain persistence, authorization, tenancy, jobs, and presentation adapters remain behind this package's public boundary; the matching API, Filament, and Livewire packages are optional adapters and never become core dependencies.

Relationship endpoints are reconciled after a People `PersonMerged` event. Duplicate edges are
removed, self-edges are discarded, and surviving edges are moved to the primary person within a
transaction.

- Composer package: `liberusoftware/module-genealogy-relationships`
- Module installer name: `genealogy-relationships`
- Category: capability
- PHP/Laravel: PHP 8.5 / Laravel 13

The package is designed for the Liberu Composer installer and must not depend on an application's `App\\` classes.
