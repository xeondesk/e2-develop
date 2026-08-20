# Nexo Foundation v0.1

Bootable foundation for Nexo, an API-first Content Operating System.

## Repository Structure

```
├── apps/           # API, CLI, and future web/admin/studio applications
├── packages/       # Core domain packages (kernel, domain, events, schema, content, database)
├── migrations/     # PostgreSQL migrations
├── tests/          # PHPUnit tests
├── docs/           # Architecture Decision Records and foundation contract
├── blueprint/      # Nexo blueprint documents (vision, architecture, migration, etc.)
├── legacy/e2/      # Legacy E2 blog engine (reference/compatibility layer)
└── docker-compose.yml, Dockerfile, .env.example, composer.json, package.json
```

## Scope
Kernel, configuration, domain primitives, PostgreSQL integration, migrations,
schema registry, content model, events, identity/authorization boundaries,
REST API, CLI, observability, tests, Docker, and isolated legacy adapter.

## Vertical slice
Schema -> Content Type -> Entry -> Validation -> Repository -> PostgreSQL ->
ContentCreated -> Audit -> REST API.

## Quick Start

```sh
# Start Nexo API (PostgreSQL)
docker compose up -d --build

# Run CLI doctor
php apps/cli/bin/nexo doctor

# Run tests
composer install && vendor/bin/phpunit
```

## Legacy E2

The original E2 blog engine is preserved in `legacy/e2/`. It runs independently
with its own Docker Compose (MySQL + Apache). See `legacy/e2/README.md` for details.

## Blueprint

The `blueprint/` directory contains the Nexo architecture and design documents.