# Changelog

## Unreleased

- Add GEDCOM 7.0 and GEDCOM X JSON export formats across the domain, API, Livewire, and Filament boundaries.

- Add a tenant-scoped, audited export action for GEDCOM and GRAMPS XML, including completion
  metadata, checksums, record counts, and failure recovery state.
- Add an auditable, transactional undo window for completed imports across the domain, API,
  Filament, and Livewire surfaces.
- Record validation and transactional import failures on the owning transfer for operator recovery.

## 1.0.0

- Initial documented module boundary.
