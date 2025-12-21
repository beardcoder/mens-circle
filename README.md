# Men's Circle 2026

Website für die Verwaltung und Organisation von Männerkreis-Veranstaltungen.

## Features

- **Event-Management** – Veranstaltungen erstellen, verwalten und veröffentlichen
- **Anmeldungen** – Teilnehmer können sich online für Events registrieren
- **Newsletter** – Abonnentenverwaltung mit An- und Abmeldemöglichkeit
- **CMS** – Seiten für Inhalte, Impressum und Datenschutz
- **Admin-Panel** – Filament-basierte Verwaltungsoberfläche

## Tech Stack

- Laravel 12
- Filament 4
- Livewire 3
- Tailwind CSS
- SQLite / MySQL

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build
```

## Development

```bash
composer run dev
```

Startet Server, Queue Worker, Logs und Vite parallel.

## License

MIT
