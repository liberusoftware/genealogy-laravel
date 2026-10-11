# Contributing to Liberu Genealogy

Thank you for your interest in contributing to Liberu Genealogy! This document provides guidelines and instructions for contributing.

## Before You Start

- Create a new branch from `main` for every new feature, bug fix, or change
- Keep changes focused — one concern per pull request
- Search existing issues and PRs before creating new ones

## Development Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and npm
- A supported database (MySQL, PostgreSQL, or SQLite)

### Installation

1. Clone the repository and enter the directory:

   ```bash
   git clone https://github.com/liberusoftware/genealogy-laravel.git
   cd genealogy-laravel
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Install JavaScript dependencies:

   ```bash
   npm install
   ```

4. Copy the environment file and generate an app key:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Configure your database in `.env`, then run migrations:

   ```bash
   php artisan migrate
   ```

6. Build front-end assets:

   ```bash
   npm run build
   ```

## Running Tests

Execute the full test suite with:

```bash
vendor/bin/pest
```

Check code style with Laravel Pint:

```bash
vendor/bin/pint --test
```

Run static analysis with PHPStan:

```bash
vendor/bin/phpstan analyse
```

## Code Style

- Format code using [Laravel Pint](https://laravel.com/docs/pint): `vendor/bin/pint`
- Follow [PHPStan](https://phpstan.org/) rules as configured in the project
- Write tests using [Pest PHP](https://pestphp.com/)
- Keep methods small and focused; prefer descriptive names

## Pull Request Process

1. Create a feature branch from `main`
2. Make your changes, including tests for new functionality
3. Ensure the full test suite passes
4. Update relevant documentation
5. Submit a pull request with a clear title and description
6. Respond to review feedback

## Documentation Requirements

- Update `docs/` documentation when adding or changing features
- Include docblocks for new classes and methods
- Update the README if setup instructions change

## Reporting Security Issues

Please do **not** open public issues for security vulnerabilities. See [SECURITY.md](SECURITY.md) for how to report them privately.
