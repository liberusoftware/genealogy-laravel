# Liberu package release map

This application owns the genealogy and Liberu cross-product package families
under the `liberusoftware` Composer vendor. Each capability is split into a
framework-neutral package and matching `-api`, `-filament`, and `-livewire`
adapters. GitHub repositories use the same `module-` prefix as the Composer
package name; the host application is not published as a package.

The source-of-truth branch for package work is `development` until the final
promotion. The former application `main` is preserved as the remote `old`
branch. Package releases must be tagged from the promoted `main` commit and
submitted with `scripts/submit-packagist.php` using `PACKAGIST_USERNAME` and
`PACKAGIST_API_TOKEN` supplied by the deployment secret manager.

Before submission, run:

```bash
composer validate --strict
php artisan module:validate
vendor/bin/pint --test
php artisan test --compact
php scripts/submit-packagist.php --dry-run
```

The release workflow must verify the exact tag commit and a clean install of
each package. No credentials, local path repositories, environment files, or
generated application data may be published.
