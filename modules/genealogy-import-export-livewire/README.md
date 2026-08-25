# Genealogy ImportExport livewire

This package is the one-to-one **livewire** presentation adapter for `liberusoftware/module-genealogy-import-export`.

It owns only livewire transport/presentation integration. Domain rules, persistence, authorization, tenancy, and lifecycle behavior remain in the matching core package.

The `module-genealogy-import-export::data-transfer-export` component validates the requested
format and delegates export to the core `ExportGenealogyData` action. It exposes only the
download and completion event; transfer status and audit metadata remain domain-owned.

- Composer package: `liberusoftware/module-genealogy-import-export-livewire`
- Installer name: `genealogy-import-export-livewire`
- Package type: `liberu-module`
