# Liberu Platform Orchestration

This independent Liberu module owns the provider-neutral **Liberu Platform Orchestration** capability.

It exposes a stable capability descriptor and service provider. Domain persistence, authorization, tenancy, jobs, and presentation adapters remain behind this package's public boundary; the matching API, Filament, and Livewire packages are optional adapters and never become core dependencies.

- Composer package: `liberusoftware/module-liberu-platform-orchestration`
- Module installer name: `liberu-platform-orchestration`
- Category: capability
- PHP/Laravel: PHP 8.5 / Laravel 13

The package is designed for the Liberu Composer installer and must not depend on an application's `App\\` classes.

