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

## MCP Access for Claude / ChatGPT

This project now exposes the AI management server in two ways:

- **Local stdio MCP** for local tools via `/home/runner/work/mens-circle/mens-circle/.mcp.json` and `/home/runner/work/mens-circle/mens-circle/opencode.json`
- **Remote HTTP MCP** at `/mcp` for hosted Claude / ChatGPT-style clients

### Remote MCP endpoint

When the app is deployed, the remote MCP endpoint is:

```text
https://your-domain.example/mcp
```

Protect it with a bearer token in `.env`:

```dotenv
AI_MANAGEMENT_TOKEN=replace-with-a-long-random-token
```

The remote MCP route is rate-limited and uses the same AI access guard as the `/api/ai/*` endpoints.

### Local MCP usage

For local development, start the existing stdio server:

```bash
php artisan mcp:start mens-circle-ai
```

Or use the already-checked-in config files:

- `/home/runner/work/mens-circle/mens-circle/.mcp.json`
- `/home/runner/work/mens-circle/mens-circle/opencode.json`

### Claude remote MCP setup

In a Claude client that supports **remote MCP servers**, register the server URL and bearer token:

```json
{
  "mcpServers": {
    "mens-circle-remote": {
      "type": "http",
      "url": "https://your-domain.example/mcp",
      "headers": {
        "Authorization": "Bearer replace-with-a-long-random-token"
      }
    }
  }
}
```

If your Claude client only supports local stdio MCP servers, keep using:

```json
{
  "mcpServers": {
    "mens-circle-ai": {
      "command": "php",
      "args": ["artisan", "mcp:start", "mens-circle-ai"]
    }
  }
}
```

### ChatGPT remote MCP setup

Use the same remote MCP URL in any ChatGPT-compatible MCP client or gateway:

- **URL:** `https://your-domain.example/mcp`
- **Auth header:** `Authorization: Bearer <AI_MANAGEMENT_TOKEN>`

If you are testing locally, expose your app temporarily with a tunnel such as `ngrok` or `cloudflared`, then point Claude / ChatGPT to the public `/mcp` URL.

### Quick verification

After deployment, verify the route is reachable:

```bash
curl -i https://your-domain.example/mcp
```

Expected result:

- `405 Method Not Allowed` for a plain GET request
- authenticated POST requests from an MCP client are accepted

### Important notes

- The MCP server returns **structured JSON**, not rendered HTML
- Write operations still require explicit confirmation in the tool arguments
- German content generation is the default
- Keep `AI_MANAGEMENT_TOKEN` private and rotate it if it is exposed
- For browser-based third-party integrations, place the app behind TLS and configure any required reverse-proxy / CORS rules in your deployment layer

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

## License

MIT
