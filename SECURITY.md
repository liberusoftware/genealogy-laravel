# Security Policy — Liberu Genealogy

## Reporting a Vulnerability

We take security seriously. If you discover a security vulnerability in Liberu Genealogy, please report it privately.

- Open a [private GitHub security advisory](https://github.com/liberusoftware/genealogy-laravel/security/advisories/new) on this repository
- Do not create a public issue for any security concern

## What to Expect

Liberu Genealogy is maintained by volunteers. While we will do our best to respond promptly, there is no guaranteed response time or SLA for security reports.

## Scope

The following are in scope for security reports:

- `app/` — application code
- `routes/` — route definitions
- `database/` — migrations and seeders
- `config/` — configuration files
- Other first-party code in this repository

## Out of Scope

The following are out of scope:

- Third-party dependencies (report to their respective maintainers)
- Vulnerabilities requiring `APP_DEBUG=true` in production
- Social engineering or physical attacks
- Denial-of-service attacks against infrastructure

## Supported Versions

Security fixes are applied to the latest major version. Please ensure you are running the most recent release before reporting.
