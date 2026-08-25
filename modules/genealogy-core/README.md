# Genealogy Genealogy Core

This independent Liberu module owns the provider-neutral **Genealogy Genealogy Core** capability.

It exposes trees with team ownership, stable per-team identifiers, privacy defaults, terminology metadata, lifecycle validation, policy checks, and post-commit lifecycle events. Domain persistence, authorization, tenancy, jobs, and presentation adapters remain behind this package's public boundary; the matching API, Filament, and Livewire packages are optional adapters and never become core dependencies.

- Composer package: `liberusoftware/module-genealogy-core`
- Module installer name: `genealogy-core`
- Category: capability
- PHP/Laravel: PHP 8.5 / Laravel 13

The package is designed for the Liberu Composer installer and must not depend on an application's `App\\` classes.
