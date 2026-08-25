# Genealogy Tree Viewer

This independent Liberu module owns the provider-neutral **Genealogy Tree Viewer** capability.

It exposes a stable capability descriptor and service provider. Trees are team-scoped, private by default, and may reference a root person. `Queries\TreeGraph` provides bounded ancestor/descendant traversal over the Relationships module's directed `parent` edges, prevents cycles, and supports excluding living people for public trees. Domain persistence, authorization, tenancy, jobs, and presentation adapters remain behind this package's public boundary; the matching API, Filament, and Livewire packages are optional adapters.

- Composer package: `liberusoftware/module-genealogy-tree-viewer`
- Module installer name: `genealogy-tree-viewer`
- Category: capability
- PHP/Laravel: PHP 8.5 / Laravel 13

The package is designed for the Liberu Composer installer and must not depend on an application's `App\\` classes.
