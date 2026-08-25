# Changelog

## Unreleased

- Persist consent-gated stored-kit comparisons as reciprocal DNA matches with shared segments,
  preserving idempotency for repeated matching runs.
- Add tenant-scoped DNA provider registry with lifecycle actions and kit references.
- Restore encrypted raw-DNA import storage, metadata, and cleanup through the module boundary.
- Restore consent-gated comparison of encrypted imported kits through the provider-neutral matching pipeline.
- Complete DNA segment lifecycle actions with tenant-safe update and deletion boundaries.

- Bound DNA group pagination and return explicit group resource envelopes.
- Add tenant-validated DNA notes and person relationship annotations.
- Route DNA kit CRUD mutations through tenant-safe domain actions.
- Route DNA annotation deletion through tenant-safe domain actions.
- Route DNA relationship edits through a tenant-safe domain action.

## Unreleased

- Carry over autosomal segment matching and cM-based relationship estimation from the legacy DNA
  workflow, with a reusable analysis service.
- Add three-way segment triangulation for shared-group discovery.
- Add bounded in-memory validation and format detection for legacy DNA file headers.

## 1.0.0

- Initial documented module boundary.
