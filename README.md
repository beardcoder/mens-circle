# Men's Circle 2026

Website für die Verwaltung und Organisation von Männerkreis-Veranstaltungen.

## Features

- **Event-Management** – Veranstaltungen erstellen, verwalten und veröffentlichen
- **Anmeldungen** – Teilnehmer können sich online für Events registrieren
- **Newsletter** – Abonnentenverwaltung mit An- und Abmeldemöglichkeit
- **CMS** – Seiten für Inhalte, Impressum und Datenschutz
- **Admin-Panels** – Dual Admin Interface:
  - **Filament** unter `/admin` (primary)
  - **MoonShine** unter `/moonshine` (parallel)

## Tech Stack

- Laravel 12
- Filament 5 (primary admin panel)
- MoonShine 4 (parallel admin panel)
- Livewire 3
- Tailwind CSS
- SQLite / MySQL

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan moonshine:install  # MoonShine setup
php artisan moonshine:user     # Create MoonShine admin user
bun install && bun run build
```

## Admin Panels

Nach dem Setup sind zwei Admin-Panels verfügbar:

- **Filament**: `http://localhost/admin` - Primary admin panel mit vollständiger Funktionalität
- **MoonShine**: `http://localhost/moonshine` - Parallel admin panel als Proof-of-Concept

Beide Panels verwenden separate Authentifizierung und können unabhängig voneinander verwendet werden.

Siehe [docs/moonshine-migration.md](docs/moonshine-migration.md) für Details zur MoonShine-Integration.

## Development

```bash
composer run dev
```

Startet Server, Queue Worker, Logs und Vite parallel.

## License

MIT
