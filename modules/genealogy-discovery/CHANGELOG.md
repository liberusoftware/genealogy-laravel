# Changelog

## Unreleased

- Persist tenant-scoped duplicate-scan candidates through an idempotent domain action while
  retaining the read-only candidate query for previews.
- Add an optional external-record provider contract and weighted, provider-neutral candidate scorer.
- Add explicit discovery match review transitions and past-tense review events.
- Route discovery match updates and deletion through tenant-safe domain actions and lifecycle events.
- Route Discovery Filament persistence through the domain lifecycle actions.

## 1.0.0

- Initial documented module boundary.
