# Männerkreis Niederbayern / Straubing

Community-Plattform für Veranstaltungen, Anmeldungen, Newsletter und CMS-Seiten. Laravel 12, PHP 8.5, Filament v5, SQLite, FrankenPHP.

## Setup

```bash
composer run setup
```

Richtet Abhängigkeiten, `.env`, Migrations und Frontend ein. Anschließend `.env` mit Mail (SMTP), SMS (`SEVEN_API_KEY`), Analytics (`UMAMI_WEBSITE_ID`) und Sentry konfigurieren.

## Entwicklung

```bash
composer run dev   # Server, Queue, Logs, Scheduler, Vite (parallel)
```

## Qualitätssicherung

```bash
composer run qa        # Format-Check, PHPStan (Level 9), Rector
composer run qa:fix    # Format + Rector automatisch fixen
composer run review    # QA + Tests

bun run lint           # ESLint
bun run format         # Prettier + Stylelint
bun run typecheck      # TypeScript
```

## Tests

```bash
composer run test
php artisan test --compact --filter="event"
```

Pest v4, In-Memory SQLite, PHPAt-Architektur-Tests.

## Deployment

```bash
docker build -t mens-circle .
docker run -p 8080:8080 mens-circle
```

Multi-Stage-Dockerfile: Bun (Frontend), Composer (PHP-Deps), FrankenPHP (Produktion).
