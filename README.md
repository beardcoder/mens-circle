# Männerkreis Niederbayern / Straubing

A community platform for organizing men's circle events in Lower Bavaria/Straubing, Germany. Handles event management, participant registration, newsletter distribution, testimonials, and dynamic CMS pages.

## Features

- **Event Management** — Create, publish, and manage events with images, capacity limits, waitlists, and automatic reminders (email + SMS)
- **Registration** — Online registration with confirmation emails, SMS notifications, and admin panel oversight
- **Newsletter** — Subscription management, welcome emails, campaign sending (chunked jobs), and secure token-based unsubscribe
- **Testimonials** — Public submission form with moderation before publication
- **CMS Pages** — Dynamic page builder with content blocks (hero, text, FAQ, journey steps, etc.)
- **Admin Panel** — Filament v5 dashboard with statistics widgets, health monitoring, log viewer, and media library

## Tech Stack

| Category    | Technology                                               |
| ----------- | -------------------------------------------------------- |
| Framework   | Laravel 12, PHP 8.5                                      |
| Admin       | Filament v5 (Livewire 4, Alpine.js)                     |
| Frontend    | Blade, vanilla TypeScript, Vite 8                        |
| Styling     | Custom CSS (OKLCH colors, native nesting) + Tailwind v4  |
| Database    | SQLite                                                   |
| Deployment  | Docker with FrankenPHP (`serversideup/php:8.5-frankenphp`) |
| Quality     | PHPStan (level 9), php-cs-fixer, Rector, ESLint, Prettier, Stylelint |
| Monitoring  | Sentry, Spatie Health, Umami Analytics                   |

## Prerequisites

- PHP 8.5+
- Composer
- Bun (for frontend tooling)
- SQLite

## Setup

```bash
# Quick setup (installs dependencies, generates key, runs migrations, builds frontend)
composer run setup

# Or manually:
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
bun install
bun run build
```

Configure `.env` with your mail (SMTP), SMS (Seven.io), analytics (Umami), and error tracking (Sentry) settings.

## Development

```bash
# Start all services (server, queue, logs, scheduler, vite)
composer run dev
```

### Individual Commands

```bash
php artisan serve          # Web server (port 8000)
php artisan queue:listen   # Queue worker
php artisan pail           # Real-time log viewer
bun run dev                # Vite HMR dev server
```

## Code Quality

```bash
# PHP
composer run format         # php-cs-fixer
composer run lint           # PHPStan (level 9)
composer run rector         # Rector refactoring
composer run qa             # All checks (format + lint + rector)
composer run qa:fix         # Auto-fix (format + rector)

# Frontend
bun run lint               # ESLint
bun run format             # Prettier (TS, Blade, CSS)
bun run typecheck          # TypeScript
bun run stylelint          # CSS

# Full review (QA + tests)
composer run review
```

## Testing

```bash
php artisan test --compact                    # All tests
php artisan test --compact --filter="event"   # Filter by name
composer run test                             # Via composer script
```

Tests use Pest v4 with in-memory SQLite. Architecture tests (PHPat) enforce layer isolation.

## Project Structure

```
app/
├── Checks/              # Health checks (Mail, Queue, SMS)
├── Console/Commands/    # SendEventReminders, GenerateSitemap
├── Contracts/           # DefinesCacheUrls
├── Enums/               # RegistrationStatus, NewsletterStatus, SocialLinkType
├── Filament/            # Admin panel (Resources, Pages, Widgets)
├── Http/
│   ├── Controllers/     # Event, Page, Newsletter, Testimonial, Socialite, Llms
│   ├── Middleware/       # CompressHtml
│   └── Requests/        # Form validation with German error messages
├── Jobs/                # SendNewsletterJob
├── Mail/                # Email templates
├── Models/              # Eloquent models with ClearsResponseCache
├── Observers/           # RegistrationObserver
├── Providers/           # App, AdminPanel
├── Services/            # EventNotificationService
├── Settings/            # GeneralSettings (Spatie)
└── Traits/              # HasEnumOptions, ClearsResponseCache

resources/css/           # Component-based CSS (OKLCH palette)
├── base/                # Variables, fonts, reset, typography
├── components/          # Buttons, header, footer, cards, forms, modal, toast
├── sections/            # Hero, intro, journey, FAQ, newsletter, event, etc.
└── utilities/           # Layout, animations, view-transitions, print

resources/js/            # Vanilla TypeScript
├── components/          # Navigation, forms, accordion, calendar, animations
└── utils/               # Helpers, toast, Umami analytics
```

## Deployment

The project includes a multi-stage `Dockerfile`:

1. **Assets stage** — Bun builds frontend via Vite
2. **Vendor stage** — Composer installs PHP dependencies (no-dev)
3. **Production stage** — FrankenPHP with PHP extensions (intl, imagick, gd, exif)

```bash
docker build -t mens-circle .
docker run -p 8080:8080 mens-circle
```

Startup scripts in `docker/entrypoint.d/` clear and rebuild Laravel caches on container start (`php artisan optimize:clear` + `php artisan optimize`).

## License

MIT
