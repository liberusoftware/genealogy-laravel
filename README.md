# Liberu Genealogy

> A collaborative Laravel application for building, researching, and preserving family history.

[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)](https://www.php.net/) [![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/) [![Filament](https://img.shields.io/badge/Filament-5-FDAE4B)](https://filamentphp.com/) [![Livewire](https://img.shields.io/badge/Livewire-4-FB70A9)](https://livewire.laravel.com/)

[![Tests](https://github.com/liberusoftware/genealogy-laravel/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/liberusoftware/genealogy-laravel/actions/workflows/tests.yml) [![Install](https://github.com/liberusoftware/genealogy-laravel/actions/workflows/install.yml/badge.svg?branch=main)](https://github.com/liberusoftware/genealogy-laravel/actions/workflows/install.yml) [![Latest release](https://img.shields.io/github/v/release/liberusoftware/genealogy-laravel?sort=semver)](https://github.com/liberusoftware/genealogy-laravel/releases/latest) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.md)

Liberu Genealogy helps individuals, families, and research groups turn scattered records into a connected family history. Create family trees, document people and relationships, attach evidence, explore timelines and places, import existing data, and collaborate in a privacy-aware workspace.

## What you can do

- Build family trees with people, families, partners, children, events, and relationship calculations
- Record places and hierarchical locations for births, marriages, deaths, migrations, and other life events
- Organise sources, citations, evidence, media, and research projects around the people they support
- Import and export GEDCOM and Gramps XML data while preserving family and person events
- Explore pedigree, descendant, timeline, and family-group reports
- Manage DNA kits, groups, comparisons, matches, and triangulation workflows
- Collaborate through shared research spaces with tenant-scoped access and privacy controls
- Use the web interface through Filament and Livewire or integrate through authenticated APIs

## Requirements

| Dependency | Supported version |
|---|---|
| PHP | 8.5 |
| Laravel | 13.x |
| Filament | 5.x |
| Livewire | 4.x |
| Composer | 2.x |
| Node.js | Latest stable release |
| Database | SQLite, MySQL, MariaDB, or another Laravel-supported database |

## Quick start

```bash
git clone https://github.com/liberusoftware/genealogy-laravel.git
cd genealogy-laravel
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan migrate
php artisan serve
```

Review `.env` before migrating. Use `php artisan migrate --seed` when example data is wanted. Queue and scheduled work can be run with the Laravel commands appropriate to the selected deployment.

## Optional Premium billing

The application is fully open by default. Set `PREMIUM_ENABLED=true` for a SaaS deployment. Premium then provides a seven-day trial with Stripe checkout, priced at £2.49 monthly or £24.99 yearly. On the first checkout request, the application automatically creates one application-specific Stripe Product and the requested recurring Price, then stores the IDs locally for reuse. Set `PREMIUM_STRIPE_APPLICATION_KEY` to a stable unique identifier for the SaaS application; otherwise it is derived from the application name and URL. This keeps separate SaaS applications isolated even when they share one Stripe account. Existing Stripe Price IDs may still be supplied through `PREMIUM_STRIPE_MONTHLY_PRICE_ID` and `PREMIUM_STRIPE_YEARLY_PRICE_ID` as overrides.

Cards are required by default when Premium is enabled. Set `PREMIUM_REQUIRE_CARD=false` to offer an explicit no-card local trial instead. A cancelled trial remains available until the seven-day period ends; after expiry the account is suspended except for GEDCOM export, affiliate pages, and payment pages.

## Architecture

Genealogy is assembled from focused Composer packages. Core domain modules remain independent from presentation concerns; API, Filament, and Livewire packages provide optional adapters for each capability.

```text
Application composition
├── modules/       # Genealogy and shared Composer modules
├── app/           # Host application and integration code
├── config/        # Enabled modules and application policy
├── database/      # Host migrations and seeders
└── tests/         # Cross-module and application tests
```

The principal domain packages are:

| Area | Packages |
|---|---|
| Core genealogy | Trees, people, families, relationships, identifiers, privacy, and terminology |
| Research | Sources, evidence, citations, research projects, timelines, and places |
| Exchange | GEDCOM and Gramps XML import/export |
| DNA | Kits, groups, matches, comparisons, and triangulation |
| Collaboration | Shared spaces and research coordination |
| Presentation | Filament and Livewire interfaces plus authenticated API adapters |

Installed modules are tracked under `/modules`, with `composer.lock` pinning their releases. Generic capability changes belong in the relevant module repository; host-specific composition belongs here.

## Testing and quality

```bash
composer validate --strict
vendor/bin/pest
vendor/bin/pint --test
vendor/bin/phpstan analyse --no-progress --memory-limit=512M
npm run build
```

Run `php artisan test` for the application suite. Module-specific tests and documentation live with each module under `modules/`.

## Documentation

- [Module development](docs/MODULE_DEVELOPMENT.md)
- [Foundation compliance](docs/FOUNDATION_COMPLIANCE.md)
- [Foundation module matrix](docs/FOUNDATION_MODULE_MATRIX.md)
- [Search architecture](docs/SEARCH_ARCHITECTURE.md)
- [Notifications](docs/NOTIFICATIONS.md)

## Security

Do not report security vulnerabilities through public GitHub issues. Email `security@liberusoftware.com` with reproduction details and the affected version so the report can be handled privately.

## Contributing

Bug reports, genealogy-domain feedback, documentation improvements, and focused pull requests are welcome. Please search existing issues first, explain the problem and approach, and include tests for behaviour changes. Changes to a reusable module should be made in that module's repository and released before updating this application.

## License

Liberu Genealogy is open-source software available under the [MIT License](LICENSE.md).


### Automatic module and theme publishing

`.github/workflows/sync-packages.yml` publishes packages changed under `modules/`
or `themes/` on pushes to `main`. A manual run reconciles all tracked packages.
Each package needs a `composer.json` with a `liberusoftware/*` name. Files are
copied from Git into the root of `liberusoftware/module-<directory>` or
`liberusoftware/theme-<directory>`. Where `.liberu-meta.json` defines a
`repositoryPrefix`, that design is authoritative (for example,
`modules/module-manager` becomes `module-module-manager`). Without a configured
prefix, an existing `module-` or `theme-` directory prefix is kept. Destination
history is preserved, deleted files are removed, and unchanged packages do not
receive another commit or release. Removing an entire package leaves its public
repository intact. This workflow makes tracked package edits publishable; install
checks still require the project's Composer dependencies to resolve to those
published package contents.

Configure these secrets in the `packagist` GitHub environment (or as repository
or organization secrets available to this repository):

- `MODULE_SYNC_TOKEN`: organization-authorized GitHub token able to create public
  repositories and read/write package contents and releases across the target
  repositories. Because package snapshots include `.github/workflows`, allow
  workflow writes too. A classic PAT needs `repo` and `workflow` scopes and an
  organization role permitted to create repositories. The default `GITHUB_TOKEN`
  cannot publish across these repositories.
- `PACKAGIST_USERNAME`: Packagist account that owns or maintains these packages.
- `PACKAGIST_API_TOKEN`: that account's **main** API token, required to submit
  new packages as well as update existing packages.

New packages start at their declared stable Composer version, or `v1.0.0`.
Existing packages use the highest stable repository tag: a higher declared
Composer version wins, `BREAKING CHANGE:` or `type!:` commits bump major,
`feat:` commits bump minor, and other changes bump patch. Only commits touching
the package since the last successful sync affect this decision. The first
automated run and manual runs reconcile all packages, so canceled or failed
runs cannot silently drop package changes. Tags and commits are
pushed atomically, then a GitHub release is created and the package is submitted
to or refreshed on Packagist. Rerunning a failed job completes publication
without issuing another release for the same contents. Concurrent pushes from
other projects are rejected instead of force-pushed; rerun the failed job.

The source project and its lockfile are not rewritten by publication. Different
projects publishing the same directory name share a destination repository;
coordinate their package contents. Run the publisher's local tests with
`python3 -m unittest discover -s .github/scripts -p 'test_sync_packages.py'`.
