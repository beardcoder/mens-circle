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

- `CacheResponse` — Spatie Response Cache (auto-cleared by `ClearsResponseCache` trait on model changes)
- `CompressHtml` — HTML minification via voku/HtmlMin

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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- filament/filament (FILAMENT) - v5
- laravel/framework (LARAVEL) - v13
- laravel/octane (OCTANE) - v2
- laravel/prompts (PROMPTS) - v0
- laravel/socialite (SOCIALITE) - v5
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2
- eslint (ESLINT) - v10
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `socialite-development` — Manages OAuth social authentication with Laravel Socialite. Activate when adding social login providers; configuring OAuth redirect/callback flows; retrieving authenticated user details; customizing scopes or parameters; setting up community providers; testing with Socialite fakes; or when the user mentions social login, OAuth, Socialite, or third-party authentication.
- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: test()/it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `medialibrary-development` — Build and work with spatie/laravel-medialibrary features including associating files with Eloquent models, defining media collections and conversions, generating responsive images, and retrieving media URLs and paths.
- `responsecache-development` — Cache entire HTTP responses using spatie/laravel-responsecache, including standard caching, flexible (stale-while-revalidate) caching, cache profiles, replacers, and selective cache clearing.
- `debug-using-debugbar` — Use this skill to optimize requests or debug Laravel application issues — slow pages, N+1 queries, exceptions, failed requests, or unexpected behavior — by inspecting data captured by Laravel Debugbar via Artisan CLI commands. Use when the user asks to investigate a bug, diagnose a slow request, find duplicate queries, check what happened on a previous request, or optimize database performance, even if they don't explicitly mention "debugbar" or "profiling."

- `laravel-specialist` — Use when building Laravel 10+ applications requiring Eloquent ORM, API resources, or queue systems. Invoke for Laravel models, Livewire components, Sanctum authentication, Horizon queues.
- `php-best-practices` — PHP 8.5+ modern patterns, PSR standards, and SOLID principles. Use when reviewing PHP code, checking type safety, auditing code quality, or ensuring PHP best practices. Triggers on "review PHP", "check PHP code", "audit PHP", or "PHP best practices".

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `bun run build`, `bun run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `bun run build` or ask the user to run `bun run dev` or `composer run dev`.

=== octane/core rules ===

# Octane

- Octane boots the application once and reuses it across requests, so singletons persist between requests.
- The Laravel container's `scoped` method may be used as a safe alternative to `singleton`.
- Never inject the container, request, or config repository into a singleton's constructor; use a resolver closure or `bind()` instead:

```php
// Bad
$this->app->singleton(Service::class, fn (Application $app) => new Service($app['request']));

// Good
$this->app->singleton(Service::class, fn () => new Service(fn () => request()));
```

- Never append to static properties, as they accumulate in memory across requests.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== filament/filament rules ===

## Filament

- Filament is used by this application. Follow the existing conventions for how and where it is implemented.
- Filament is a Server-Driven UI (SDUI) framework for Laravel that lets you define user interfaces in PHP using structured configuration objects. Built on Livewire, Alpine.js, and Tailwind CSS.
- Use the `search-docs` tool for official documentation on Artisan commands, code examples, testing, relationships, and idiomatic practices. If `search-docs` is unavailable, refer to https://filamentphp.com/docs.

### Artisan

- Always use Filament-specific Artisan commands to create files. Find available commands with the `list-artisan-commands` tool, or run `php artisan --help`.
- Always inspect required options before running a command, and always pass `--no-interaction`.

### Patterns

Always use static `make()` methods to initialize components. Most configuration methods accept a `Closure` for dynamic values.

Use `Get $get` to read other form field values for conditional logic:

<code-snippet name="Conditional form field visibility" lang="php">
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

Select::make('type')
    ->options(CompanyType::class)
    ->required()
    ->live(),

TextInput::make('company_name')
    ->required()
    ->visible(fn (Get $get): bool => $get('type') === 'business'),

</code-snippet>

Use `state()` with a `Closure` to compute derived column values:

<code-snippet name="Computed table column value" lang="php">
use Filament\Tables\Columns\TextColumn;

TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),

</code-snippet>

Actions encapsulate a button with an optional modal form and logic:

<code-snippet name="Action with modal form" lang="php">
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

Action::make('updateEmail')
    ->schema([
        TextInput::make('email')
            ->email()
            ->required(),
    ])
    ->action(fn (array $data, User $record) => $record->update($data))

</code-snippet>

### Testing

Always authenticate before testing panel functionality. Filament uses Livewire, so use `Livewire::test()` or `livewire()` (available when `pestphp/pest-plugin-livewire` is in `composer.json`):

<code-snippet name="Table test" lang="php">
use function Pest\Livewire\livewire;

livewire(ListUsers::class)
    ->assertCanSeeTableRecords($users)
    ->searchTable($users->first()->name)
    ->assertCanSeeTableRecords($users->take(1))
    ->assertCanNotSeeTableRecords($users->skip(1));

</code-snippet>

<code-snippet name="Create resource test" lang="php">
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

livewire(CreateUser::class)
    ->fillForm([
        'name' => 'Test',
        'email' => 'test@example.com',
    ])
    ->call('create')
    ->assertNotified()
    ->assertRedirect();

assertDatabaseHas(User::class, [
    'name' => 'Test',
    'email' => 'test@example.com',
]);

</code-snippet>

<code-snippet name="Testing validation" lang="php">
use function Pest\Livewire\livewire;

livewire(CreateUser::class)
    ->fillForm([
        'name' => null,
        'email' => 'invalid-email',
    ])
    ->call('create')
    ->assertHasFormErrors([
        'name' => 'required',
        'email' => 'email',
    ])
    ->assertNotNotified();

</code-snippet>

<code-snippet name="Calling actions in pages" lang="php">
use Filament\Actions\DeleteAction;
use function Pest\Livewire\livewire;

livewire(EditUser::class, ['record' => $user->id])
    ->callAction(DeleteAction::class)
    ->assertNotified()
    ->assertRedirect();

</code-snippet>

<code-snippet name="Calling actions in tables" lang="php">
use Filament\Actions\Testing\TestAction;
use function Pest\Livewire\livewire;

livewire(ListUsers::class)
    ->callAction(TestAction::make('promote')->table($user), [
        'role' => 'admin',
    ])
    ->assertNotified();

</code-snippet>

### Correct Namespaces

- Form fields (`TextInput`, `Select`, etc.): `Filament\Forms\Components\`
- Infolist entries (`TextEntry`, `IconEntry`, etc.): `Filament\Infolists\Components\`
- Layout components (`Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`, etc.): `Filament\Schemas\Components\`
- Schema utilities (`Get`, `Set`, etc.): `Filament\Schemas\Components\Utilities\`
- Actions (`DeleteAction`, `CreateAction`, etc.): `Filament\Actions\`. Never use `Filament\Tables\Actions\`, `Filament\Forms\Actions\`, or any other sub-namespace for actions.
- Icons: `Filament\Support\Icons\Heroicon` enum (e.g., `Heroicon::PencilSquare`)

### Common Mistakes

- **Never assume public file visibility.** File visibility is `private` by default. Always use `->visibility('public')` when public access is needed.
- **Never assume full-width layout.** `Grid`, `Section`, and `Fieldset` do not span all columns by default. Explicitly set column spans when needed.
- **Use correct property types when overriding Page, Resource, and Widget properties.** These properties have union types or changed modifiers that must be preserved:
  - `$navigationIcon`: `protected static string | BackedEnum | null` (not `?string`)
  - `$navigationGroup`: `protected static string | UnitEnum | null` (not `?string`)
  - `$view`: `protected string` (not `protected static string`) on Page and Widget classes

=== spatie/laravel-medialibrary rules ===

## Media Library

- `spatie/laravel-medialibrary` associates files with Eloquent models, with support for collections, conversions, and responsive images.
- Always activate the `medialibrary-development` skill when working with media uploads, conversions, collections, responsive images, or any code that uses the `HasMedia` interface or `InteractsWithMedia` trait.

</laravel-boost-guidelines>
