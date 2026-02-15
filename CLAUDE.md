# CLAUDE.md — Mens Circle (TYPO3 v14.1)

## Project Overview

TYPO3 CMS v14.1 rebuild for **Männerkreis Niederbayern / Straubing**, a men's circle community website. The architecture follows KISS principles: core-first TYPO3, minimal custom fields, high performance, and easy maintenance.

**Key features:** Event management with registration, newsletter system with token-based unsubscribe, testimonial submissions, iCal export, async notifications (email + optional SMS) via TYPO3 Messenger, custom backend modules, and dashboard widgets.

## Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| CMS | TYPO3 | 14.1 |
| PHP | PHP | 8.5 |
| Database | MariaDB | 11.8 (dev via DDEV) |
| Package manager (PHP) | Composer | 2 |
| Package manager (JS) | Bun | 1.3.9 |
| Build tool | Vite | 7.3.1 |
| Frontend enhancement | Hotwired Turbo | 8.0.23 |
| Animations | Motion | 12.33.0 |
| Dev environment | DDEV | typo3 project type |
| Production | Docker | serversideup/php:8.5-fpm-nginx |

**Important:** This project uses **Bun**, not npm/yarn/pnpm. Always use `bun install` and `bun run <script>`.

## Project Structure

```
/
├── packages/mens_circle/          # Main TYPO3 sitepackage extension
│   ├── Classes/
│   │   ├── Command/               # CLI commands (import, reminders, repair)
│   │   ├── Controller/            # Extbase controllers (Event, Newsletter, Testimonial, backends)
│   │   ├── Dashboard/Provider/    # Backend dashboard data providers
│   │   ├── DataProcessing/        # Content data processors
│   │   ├── Domain/
│   │   │   ├── Enum/              # PHP enums (NotificationChannel, NotificationType, RegistrationStatus)
│   │   │   ├── Model/             # Extbase models (Event, Participant, Registration, etc.)
│   │   │   └── Repository/        # Extbase repositories
│   │   ├── Message/               # Async message DTOs
│   │   ├── MessageHandler/        # TYPO3 Messenger handlers
│   │   └── Service/               # Business logic (Mail, SMS, DateTimeFormatter, etc.)
│   ├── Configuration/
│   │   ├── Backend/               # Backend module + dashboard config
│   │   ├── FlexForms/             # Content element + plugin FlexForms
│   │   ├── Sets/MensCircle/       # TYPO3 Site Set (TypoScript, settings)
│   │   ├── TCA/                   # Table Configuration Array files
│   │   ├── Icons.php              # SVG icon registration
│   │   ├── Services.yaml          # Symfony DI configuration
│   │   └── ViteEntrypoints.json   # Vite entrypoint mapping
│   └── Resources/
│       ├── Private/
│       │   ├── Frontend/
│       │   │   ├── Scripts/       # TypeScript (components, composables, utils)
│       │   │   └── Styles/        # CSS (base, sections, components, utilities)
│       │   ├── Language/          # XLIFF 2.0 translations (de + en)
│       │   └── Templates/         # Fluid templates + layouts + partials
│       └── Public/Icons/          # SVG icons for backend
├── config/
│   ├── sites/mens-circle/         # Site config, routing, route enhancers
│   └── system/additional.php      # Env-based DB/SMTP/proxy config
├── .ddev/                         # DDEV local dev environment
├── .claude/skills/                # Specialized Claude skills (see below)
├── Dockerfile                     # Multi-stage production build
├── composer.json                  # PHP dependencies
├── package.json                   # Frontend dependencies (Bun)
├── vite.config.ts                 # Vite + vite-plugin-typo3
├── tsconfig.json                  # TypeScript config (ES2020, strict)
├── phpstan.neon                   # Static analysis config (level 5)
└── .php-cs-fixer.dist.php         # Code style config (PER-CS)
```

## Commands

### Frontend Build

```bash
bun run dev            # Vite dev server on :5173
bun run build          # Production build
bun run build:watch    # Watch mode build
bun run preview        # Preview production build on :4173
```

### PHP / TYPO3

```bash
composer cs:fix        # Fix code style (PHP-CS-Fixer)
composer cs:check      # Check code style (dry-run)
composer stan          # PHPStan static analysis (level 5)
```

### TYPO3 Runtime (prefix with `ddev exec` in dev)

```bash
vendor/bin/typo3 cache:flush                                    # Clear all caches
vendor/bin/typo3 messenger:consume doctrine                     # Run async message worker
vendor/bin/typo3 menscircle:events:dispatch-reminders            # Queue event reminders
vendor/bin/typo3 menscircle:import:laravel-sql <file> --truncate # Import legacy SQL dump
```

## Code Conventions

### PHP

- **`declare(strict_types=1);`** on every file
- **PER-CS** code style with risky rules enabled (enforced via `.php-cs-fixer.dist.php`)
- **Alphabetically ordered imports**, no unused imports
- **Trailing commas** in multiline arguments, arrays, match, and parameters
- **Single quotes** for strings
- **Concat spacing:** `$a . $b` (single space around `.`)
- **Namespace:** `BeardCoder\MensCircle\`
- **Final classes** for controllers and models
- **Constructor property promotion** with `readonly` where applicable
- **PHP 8.5 property hooks** used in models (e.g., `Event::$maxParticipants` with `set` hook)
- **Backed enums** with TitleCase keys (e.g., `RegistrationStatus::Registered`)
- **PHPStan level 5** — all code in `packages/mens_circle/Classes` must pass

### TypeScript

- **Strict mode** enabled in `tsconfig.json`
- **ES2020** target with ESNext modules
- **Path alias:** `@/*` maps to `packages/mens_circle/Resources/Private/Frontend/Scripts/*`
- **Entry point:** `App.entry.ts`

### CSS

- Organized by layer: `base/` (variables, typography, reset), `sections/`, `components/`, `utilities/`
- Entry point: `App.entry.css`

### Templates (Fluid)

- Standard TYPO3 Fluid with layouts, partials, and content-specific templates
- Hotwired Turbo-compatible rendering
- Mail-specific templates for notification emails

### TYPO3 Patterns

- **Core-first:** No custom `tt_content` SQL columns — only core fields + FlexForms
- **Domain-driven:** Models, Repositories, Enums in `Domain/` directory
- **Async messaging:** TYPO3 Messenger with Doctrine transport for notifications
- **Services:** Business logic extracted to dedicated service classes
- **Content elements:** FlexForm-based configuration in `Configuration/FlexForms/ContentElements/`
- **Site Set:** Configuration in `Configuration/Sets/MensCircle/` (TypoScript, settings definitions)
- **Route enhancers:** Extbase-based routing for events, newsletter, testimonials

### Database

- **Domain tables:** `tx_menscircle_domain_model_event`, `tx_menscircle_domain_model_participant`, `tx_menscircle_domain_model_registration`, `tx_menscircle_domain_model_newsletter_subscription`, `tx_menscircle_domain_model_testimonial`
- **TCA files** in `Configuration/TCA/` — one per domain table
- **Storage PID:** 2 (configured in site settings)

### Localization

- **XLIFF 2.0** format
- **Default language:** German (`*.xlf`)
- **English translations:** `*.en.xlf`
- Language files in `Resources/Private/Language/`

## Integrated Skills

This project has specialized Claude skills in `.claude/skills/` that activate automatically for relevant tasks:

### TYPO3 Skills
- **typo3-architect** — System architecture, multi-site setup, performance, content strategy
- **typo3-content-blocks** — Content elements, FlexForms, TCA, template integration
- **typo3-extension-dev** — Extension development, Extbase, domain models, repositories, CLI commands
- **typo3-fluid** — Fluid templates, ViewHelpers, responsive design, template optimization
- **typo3-typoscript** — TypoScript, site config, routing, caching, conditions

### General Skills
- **pest-testing** — PHP testing with Pest 4
- **php-modernizer** — PHP 8.4+ modernization with TYPO3-specific optimizations
- **tailwindcss-development** — Tailwind CSS v4 styling

These skills provide domain-specific expertise and should be activated whenever working in the relevant area.

## Known Pitfalls (Avoid Regressions)

- **DBAL4:** Use `Doctrine\DBAL\ParameterType::*` — never `PDO::PARAM_*`
- **`Connection::count()`** requires 3 arguments in TYPO3 14
- **Schema-aware inserts/updates:** Always filter fields against the actual DB schema to avoid writing to non-existent columns
- **Fluid `f:link.typolink`:** Does not accept `rel` argument — use `additionalAttributes` instead
- **SQL JSON parsing:** Do not use `stripcslashes()` on JSON from SQL dumps
- **No custom `tt_content` columns:** Content configuration goes through FlexForms only

## Environment Variables

Configured in `config/system/additional.php` via `env()` helper:

| Variable | Purpose |
|----------|---------|
| `TRUSTED_HOSTS_PATTERN` | Restrict allowed hostnames |
| `SITENAME` | TYPO3 site name |
| `TYPO3_DB_HOST`, `_PORT`, `_NAME`, `_USER`, `_PASSWORD`, `_DRIVER` | Database connection |
| `TYPO3_MAIL_TRANSPORT`, `_SMTP_SERVER`, `_SMTP_ENCRYPT`, `_SMTP_USERNAME`, `_SMTP_PASSWORD` | Mail transport |
| `REVERSE_PROXY_SSL`, `_IP`, `_HEADER_MULTI_VALUE` | Reverse proxy settings |
| `MENSCIRCLE_SMS_API_KEY` | Optional SMS delivery key |
| `DEV_IPMASK`, `DISPLAY_ERRORS`, `DEBUG` | Development settings |

## Deployment

Production uses a multi-stage Dockerfile:
1. **vendor stage** — Composer install (cached, no-dev, optimized autoloader)
2. **assets stage** — Bun install + Vite build
3. **production stage** — `serversideup/php:8.5-fpm-nginx` with intl, gd, exif extensions

## Development Setup (DDEV)

```bash
ddev start
ddev composer install
bun install
bun run build
ddev exec vendor/bin/typo3 setup
```

The DDEV configuration uses PHP 8.5, MariaDB 11.8, and nginx-fpm.
