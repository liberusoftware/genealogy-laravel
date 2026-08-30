# Genealogy Import Export

This independent Liberu module owns the provider-neutral **Genealogy Import Export** capability.

It exposes a stable capability descriptor and service provider. Domain persistence, authorization, tenancy, jobs, and presentation adapters remain behind this package's public boundary; the matching API, Filament, and Livewire packages are optional adapters and never become core dependencies.

## Import recovery

Imports are previewed and applied transactionally. A completed import records the created people,
updated-person snapshots, and new relationship identifiers in transfer metadata. For 24 hours
(`genealogy-import-export.undo_hours`), the owning team can invoke `UndoDataTransfer` or the
matching API, Filament, or Livewire action to restore updated people and remove records created by
that import. The transfer becomes `rolled_back`; expired or already undone transfers fail safely.

## Export lifecycle

`ExportGenealogyData` is the authoritative export boundary for GEDCOM 5.5.1, GEDCOM 7.0,
GEDCOM X JSON, and GRAMPS XML. It requires the active team context, serializes only that team's people and relationships, and
records an `export` transfer. Completed transfers include the record count, byte count, SHA-256
checksum, and completion timestamp; serializer failures leave the transfer marked `failed` with
an operator-safe error summary. API, Filament, and Livewire downloads all delegate to this action.

- Composer package: `liberusoftware/module-genealogy-import-export`
- Module installer name: `genealogy-import-export`
- Category: capability
- PHP/Laravel: PHP 8.5 / Laravel 13

The package is designed for the Liberu Composer installer and must not depend on an application's `App\\` classes.
