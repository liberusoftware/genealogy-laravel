# Genealogy Dna api

This package is the one-to-one **api** presentation adapter for `liberusoftware/module-genealogy-dna`.

It owns only api transport/presentation integration. Domain rules, persistence, authorization, tenancy, and lifecycle behavior remain in the matching core package.

The authenticated `/api/v1/genealogy/dna/matches/analyze` operation exposes the domain analyzer for
normalized genotype maps and returns shared segments plus relationship estimates.
Provider CRUD is available under `/api/v1/genealogy/dna/providers`; kit import is available
under `POST /api/v1/genealogy/dna/kits/import` and encrypts raw content before persistence.

- Composer package: `liberusoftware/module-genealogy-dna-api`
- Installer name: `genealogy-dna-api`
- Package type: `liberu-module`
