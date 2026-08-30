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

The application is fully open by default. Set `PREMIUM_ENABLED=true` for a SaaS deployment. Premium then provides a seven-day trial with Stripe checkout, priced at £2.49 monthly or £24.99 yearly. Stripe Price IDs for those two plans are supplied through `PREMIUM_STRIPE_MONTHLY_PRICE_ID` and `PREMIUM_STRIPE_YEARLY_PRICE_ID`; each Stripe Price should use GBP and the matching amount in pence.

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
