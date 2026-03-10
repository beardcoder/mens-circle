# Männerkreis Niederbayern / Straubing

A Laravel 12 community platform for organizing men's circle events, managing registrations, newsletters, and CMS pages. German-language application with Filament v5 admin panel.

## Tech Stack

| Category    | Technology                                               |
| ----------- | -------------------------------------------------------- |
| Framework   | Laravel 12, PHP 8.5                                      |
| Admin Panel | Filament v5 (Livewire 4, Alpine.js)                     |
| Frontend    | Blade templates, vanilla TypeScript, Vite 7              |
| Styling     | Custom CSS (OKLCH color system) + Tailwind CSS v4        |
| Database    | SQLite (local/production), migrations via Eloquent       |
| Testing     | Pest v4, PHPStan level 9, Rector (PHP 8.5 target)       |
| Formatting  | php-cs-fixer (PHP), ESLint + Prettier (TS/Blade), Stylelint (CSS) |
| Auth        | Laravel Socialite (GitHub OAuth for admin)               |
| Deployment  | Docker with FrankenPHP, `serversideup/php:8.5-frankenphp` |

## Project Structure

```
app/
├── Checks/                  # Spatie Health checks (Mail, Queue, SevenIo SMS)
├── Console/Commands/        # SendEventReminders, GenerateSitemap
├── Contracts/               # DefinesCacheUrls interface
├── Enums/                   # RegistrationStatus, NewsletterStatus, SocialLinkType, Heroicon
├── Filament/
│   ├── Forms/               # Reusable form schemas (ParticipantForms)
│   ├── Pages/               # SendNewsletter, ManageGeneralSettings, ClearCache
│   ├── Resources/           # Event, User, Participant, Registration, Newsletter,
│   │                        #   NewsletterSubscription, Testimonial, Page
│   └── Widgets/             # StatsOverview, RecentEvents, UpcomingEventRegistrations
├── Http/
│   ├── Controllers/         # Event, Page, Newsletter, TestimonialSubmission, Socialite, Llms
│   ├── Middleware/           # CompressHtml (voku/HtmlMin)
│   ├── Requests/            # EventRegistration, NewsletterSubscription, TestimonialSubmission
│   └── Resources/           # EventResource (API)
├── Jobs/                    # SendNewsletterJob (chunked, retryable)
├── Mail/                    # EventRegistrationConfirmation, EventReminder,
│                            #   AdminEventRegistrationNotification, NewsletterMail, NewsletterWelcome
├── Models/                  # User, Event, Registration, Participant, Newsletter,
│                            #   NewsletterSubscription, Testimonial, Page, ContentBlock
├── Notifications/           # HealthCheckFailedNotification (throttled)
├── Observers/               # RegistrationObserver
├── Providers/               # AppServiceProvider, AdminPanelProvider
├── Services/                # EventNotificationService (email + SMS via Seven.io)
├── Settings/                # GeneralSettings (Spatie Settings)
└── Traits/                  # HasEnumOptions, ClearsResponseCache
```

## Domain Models & Relationships

```
User (admin only, GitHub OAuth)

Event ──hasMany──> Registration ──belongsTo──> Participant
  │                                               │
  └─ scopes: published(), upcoming()              ├─ hasMany(Registration)
  └─ attrs: availableSpots, isFull, isPast        ├─ hasOne(NewsletterSubscription)
  └─ methods: generateICalContent()               └─ methods: findOrCreateByEmail()

Newsletter (subject, content, status)
NewsletterSubscription ──belongsTo──> Participant
Page ──hasMany──> ContentBlock
Testimonial (standalone, moderated)
```

## Key Workflows

**Event Registration:** `EventController::register()` → `EventRegistrationRequest` → creates/finds Participant → creates Registration → sends confirmation email + optional SMS → clears response cache.

**Newsletter:** `NewsletterController::subscribe()` → creates subscription with random token → sends welcome email. Sending: `SendNewsletter` Filament page → `SendNewsletterJob` (chunked in 100s).

**Event Reminders:** Scheduled command `events:send-reminders` runs daily at 10:00 → finds events in 24h window → sends email + SMS via `EventNotificationService`.

## Routes

All routes defined in `routes/web.php` and `routes/api.php`.

| Route                                 | Controller                              | Purpose                              |
| ------------------------------------- | --------------------------------------- | ------------------------------------ |
| `GET /`                               | PageController::home                    | Homepage with dynamic content blocks |
| `GET /event`                          | EventController::showNext               | Redirect to next upcoming event      |
| `GET /event/{slug}`                   | EventController::show                   | Event detail page                    |
| `POST /api/event/register`            | EventController::register               | JSON registration endpoint           |
| `GET /teile-deine-erfahrung`          | TestimonialSubmissionController::show   | Testimonial form                     |
| `POST /api/testimonial/submit`        | TestimonialSubmissionController::submit | Submit testimonial                   |
| `POST /api/newsletter/subscribe`      | NewsletterController::subscribe         | JSON subscription endpoint           |
| `GET /newsletter/unsubscribe/{token}` | NewsletterController::unsubscribe       | Token-based unsubscribe              |
| `GET /auth/{provider}/redirect`       | SocialiteController::redirect           | GitHub OAuth (admin)                 |
| `GET /auth/{provider}/callback`       | SocialiteController::callback           | OAuth callback                       |
| `GET /llms.txt`                       | LlmsController::show                    | LLM-friendly site description        |
| `GET /{slug}`                         | PageController::show                    | Dynamic CMS pages (catch-all, last)  |

Scheduled commands in `routes/console.php`:

- `events:send-reminders` — daily at 10:00
- `sitemap:generate` — daily at 02:00
- Health checks — every minute

## Testing

Tests use Pest v4 with `expect()` syntax. All tests use in-memory SQLite (`RefreshDatabase`).

```bash
# Run all tests
php artisan test --compact

# Run specific test file
php artisan test --compact tests/Feature/Controllers/EventControllerTest.php

# Run by filter
php artisan test --compact --filter="can register for event"
```

### Test Structure

```
tests/
├── Architecture/            # PHPat layer dependency tests
│   ├── LayerTest.php        # Models, Services, Enums, Actions, Mail isolation
│   ├── CodeQualityTest.php  # Settings final, Models independent of Filament
│   └── NamingTest.php       # Service naming conventions
├── Feature/
│   ├── Api/                 # API route tests
│   ├── Commands/            # SendEventReminders
│   ├── Controllers/         # EventController, NewsletterController, AnalyticsProxy
│   ├── Mail/                # AdminEventRegistrationNotification, EventParticipantMessage
│   ├── Middleware/           # CompressHtml
│   ├── ClearsResponseCacheTest.php
│   ├── HealthCheckNotificationThrottleTest.php
│   ├── LlmControllerArchetypesTest.php
│   ├── QueueHealthCheckTest.php
│   ├── ScheduleHealthCheckIntegrationTest.php
│   └── SeoIndexingSignalsTest.php
└── Unit/
    ├── Enums/               # EmailTemplate
    ├── Models/              # Event, Participant, Registration
    └── Services/            # EmailTemplateService
```

### Factory States

| Factory                | Key States                                                                                  |
| ---------------------- | ------------------------------------------------------------------------------------------- |
| Event                  | `published()`, `unpublished()`, `tomorrow()`, `past()`, `onDate()`                          |
| Registration           | `registered()`, `waitlist()`, `cancelled()`, `attended()`, `forEvent()`, `forParticipant()` |
| Participant            | `withPhone()`                                                                               |
| Page                   | `published()`, `unpublished()`                                                              |
| ContentBlock           | `hero()`, `text()`, `forPage()`                                                             |
| Newsletter             | `draft()`, `sending()`, `sent()`                                                            |
| NewsletterSubscription | `active()`, `unconfirmed()`, `unsubscribed()`, `forParticipant()`                           |
| Testimonial            | `unpublished()`, `anonymous()`                                                              |

## Code Quality Commands

```bash
# Format PHP code (php-cs-fixer, NOT Pint)
composer run format

# Check formatting without fixing
composer run format:check

# Static analysis (level 9)
composer run lint

# Rector dry-run (PHP 8.5 modernization)
composer run rector:check

# Run all QA checks
composer run qa

# Fix all QA issues
composer run qa:fix

# Full review (QA + tests)
composer run review

# Frontend lint + format + typecheck
bun run lint
bun run format
bun run typecheck
bun run stylelint
```

## Frontend Architecture

Server-rendered Blade with progressive enhancement via vanilla TypeScript. No Inertia, React, or Vue.

### CSS

Custom component-based CSS in `resources/css/` with OKLCH color palette:

- `base/` — variables (OKLCH colors, spacing, typography), fonts, reset, typography
- `components/` — buttons, header, footer, cards, accordion, forms, modal, toast
- `sections/` — hero, intro, moderator, journey, archetypes, testimonials, testimonial-form, faq, newsletter, whatsapp, cta, event, no-event, error, legal
- `utilities/` — layout, animations, visual, view-transitions, print

Tailwind CSS v4 is also available for utility classes.

### TypeScript

Entry point: `resources/js/app.ts`.

- `components/` — navigation, forms, scroll-animations, accordion, calendar
- `utils/` — helpers, toast, umami analytics

### Build

```bash
bun run dev    # Vite dev server with HMR
bun run build  # Production build to public/build/
```

## Content Block System

Pages use a dynamic block system. Supported types in `ContentBlock.type`:
`hero`, `text_section`, `intro`, `value_items`, `journey_steps`, `moderator`, `cta`, `newsletter`, `whatsapp_community`, `testimonials`, `faq`

Each block has a corresponding Blade component in `resources/views/components/blocks/`.

## Configuration

### Key Custom Config Files

| File                       | Purpose                               |
| -------------------------- | ------------------------------------- |
| `config/sevenio.php`       | Seven.io SMS API settings             |
| `config/analytics.php`     | Umami analytics (disabled by default) |
| `config/health.php`        | Spatie Health email notifications     |
| `config/responsecache.php` | Response cache (7-day TTL)            |

### Environment

- Database: SQLite (`DB_CONNECTION=sqlite`)
- Cache: Failover (octane → file), response cache enabled
- Queue: Database driver
- Session: Database driver
- Mail: SMTP with admin notification address in `MAIL_ADMIN_ADDRESS`
- SMS: Seven.io with `SEVEN_API_KEY` and `SEVEN_FROM`
- Analytics: Umami with `UMAMI_WEBSITE_ID` and `UMAMI_SCRIPT_URL`

## Middleware Stack

Configured in `bootstrap/app.php`:

- `CompressHtml` — HTML minification via voku/HtmlMin
- `CacheResponse` — Spatie Response Cache (auto-cleared by `ClearsResponseCache` trait on model changes)

## Key Third-Party Packages

| Package                        | Purpose                                                    |
| ------------------------------ | ---------------------------------------------------------- |
| `nunomaduro/essentials`        | Strict models, CarbonImmutable, HTTPS, safe console        |
| `spatie/laravel-responsecache` | Full HTTP response caching                                 |
| `spatie/laravel-medialibrary`  | Event images, content block media                          |
| `spatie/laravel-settings`      | Persistent `GeneralSettings` (site name, contact, socials) |
| `spatie/laravel-sluggable`     | Auto-slug for Events and Pages                             |
| `spatie/laravel-health`        | Health checks (mail, queue, SMS, disk, DB)                 |
| `spatie/laravel-sitemap`       | Sitemap generation                                         |
| `seven.io/api`                 | SMS notifications for event reminders                      |
| `sentry/sentry-laravel`        | Error tracking                                             |
| `voku/html-min`                | HTML response compression                                  |

## Conventions

- All user-facing text is in German (labels, validation messages, emails, enums)
- Enum keys are TitleCase; enum labels return German translations via `getLabel()`
- Business logic belongs in `Actions/` classes, not controllers
- Models use the `ClearsResponseCache` trait to auto-invalidate cache
- Form validation uses dedicated `FormRequest` classes with German error messages
- Soft deletes on: Event, Registration, NewsletterSubscription, Testimonial, Page, ContentBlock
- View composers inject `$settings` (GeneralSettings) and `$hasNextEvent` to all views
- PHP formatting uses php-cs-fixer (not Pint); config in `.php-cs-fixer.dist.php`
- `nunomaduro/essentials` makes `now()` return `CarbonImmutable` — use `DateTimeInterface` for type hints
