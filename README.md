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

Startup scripts in `docker/entrypoint.d/` handle cache clearing on container start.

## Varnish Cache

The application integrates with [Varnish](https://varnish-cache.org/) via [`spatie/laravel-varnish`](https://github.com/spatie/laravel-varnish) for edge caching. Public routes automatically receive `X-Cacheable` and `Cache-Control: s-maxage` headers.

### Environment Variables

```env
VARNISH_HOST=mens-circle.de       # Hostname(s), comma-separated for multiple
VARNISH_ADMIN_SECRET=/etc/varnish/secret
VARNISH_ADMIN_PORT=6082
VARNISH_CACHE_TIME=1440            # Cache TTL in minutes (default: 1 day)
```

### Flush Varnish Cache

```bash
php artisan varnish:flush               # Flush everything
php artisan varnish:flush "/event/.*"   # Flush by URL regex
```

The admin panel ("Cache löschen") also has a dedicated Varnish flush button.

### VCL Configuration (Varnish 6.x+)

Mount this as `/etc/varnish/default.vcl` in your Varnish container:

```vcl
vcl 4.1;

# ── Backend: FrankenPHP application server ───────────────────────────────
backend default {
    .host = "app";
    .port = "443";
    .connect_timeout = 5s;
    .first_byte_timeout = 30s;
    .between_bytes_timeout = 10s;
    .probe = {
        .url = "/up";
        .timeout = 3s;
        .interval = 15s;
        .window = 5;
        .threshold = 3;
    }
}

# ── ACL: hosts allowed to issue BAN/PURGE requests ──────────────────────
acl purge {
    "localhost";
    "127.0.0.1";
    "::1";
    "app";
}

# ── vcl_recv ─────────────────────────────────────────────────────────────
sub vcl_recv {
    # BAN support (used by spatie/laravel-varnish)
    if (req.method == "BAN") {
        if (!client.ip ~ purge) {
            return (synth(405, "Not allowed."));
        }
        ban("req.http.host ~ " + req.http.X-Ban-Host
            + " && req.url ~ " + req.http.X-Ban-URL);
        return (synth(200, "Banned"));
    }

    # PURGE support (single URL invalidation)
    if (req.method == "PURGE") {
        if (!client.ip ~ purge) {
            return (synth(405, "Not allowed."));
        }
        return (purge);
    }

    # Only cache GET and HEAD
    if (req.method != "GET" && req.method != "HEAD") {
        return (pass);
    }

    # Never cache admin panel, auth, newsletter unsubscribe, API, health, Livewire
    if (req.url ~ "^/admin"
        || req.url ~ "^/auth/"
        || req.url ~ "^/newsletter/unsubscribe/"
        || req.url ~ "^/api/"
        || req.url ~ "^/up$"
        || req.url ~ "^/livewire/") {
        return (pass);
    }

    # Strip cookies from public requests to enable caching
    unset req.http.Cookie;
    return (hash);
}

# ── vcl_backend_response ─────────────────────────────────────────────────
sub vcl_backend_response {
    # Cache responses with X-Cacheable header (set by Laravel middleware)
    if (beresp.http.X-Cacheable ~ "1") {
        unset beresp.http.Set-Cookie;

        # Fall back to 1 day if s-maxage not set
        if (beresp.http.Cache-Control !~ "s-maxage") {
            set beresp.ttl = 86400s;
        }

        set beresp.grace = 1h;
        set beresp.keep = 7d;
    }

    # Never cache 5xx errors
    if (beresp.status >= 500) {
        set beresp.ttl = 0s;
        set beresp.uncacheable = true;
        return (deliver);
    }

    # Cache static assets aggressively (30 days)
    if (bereq.url ~ "\.(css|js|jpg|jpeg|png|gif|ico|svg|webp|avif|woff2?|ttf|eot)(\?.*)?$") {
        set beresp.ttl = 30d;
        unset beresp.http.Set-Cookie;
        set beresp.grace = 1h;
    }
}

# ── vcl_deliver ──────────────────────────────────────────────────────────
sub vcl_deliver {
    # Debug headers (remove these in production)
    if (resp.http.X-Cacheable) {
        if (obj.hits > 0) {
            set resp.http.X-Varnish-Cache = "HIT";
            set resp.http.X-Varnish-Hits = obj.hits;
        } else {
            set resp.http.X-Varnish-Cache = "MISS";
        }
    }

    # Remove internal headers from public responses
    unset resp.http.X-Cacheable;
    unset resp.http.X-Powered-By;
    unset resp.http.Server;
    unset resp.http.Via;
}
```

Adjust `backend default` host/port to match your Docker Compose service names.

## License

MIT
