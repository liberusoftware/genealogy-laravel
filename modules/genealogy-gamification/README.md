# Genealogy Gamification

This independent module provides tenant-scoped points, achievements, unlock records,
progress tracking, user statistics, and leaderboard queries for genealogy research activity.
It contains domain logic only; hosts may add their own Filament or Livewire presentation.

- Composer package: `liberusoftware/module-genealogy-gamification`
- Installer name: `genealogy-gamification`
- Package type: `liberu-module`

Points are recorded as immutable activity entries. Totals and leaderboard positions are
derived from those entries, so a user's account model does not need gamification-specific
columns and points remain isolated by the active genealogy team.
