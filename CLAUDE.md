# Männerkreis Niederbayern / Straubing

A Laravel 12 community platform for organizing men's circle events, managing registrations, newsletters, and CMS pages. German-language application with Filament v5 admin panel.

## Tech Stack

| Category | Technology |
|----------|-----------|
| Framework | Laravel 12, PHP 8.5 |
| Admin Panel | Filament v5 (Livewire 4, Alpine.js) |
| Frontend | Blade templates, vanilla TypeScript, Vite 8 |
| Styling | Custom CSS (OKLCH color system) + Tailwind CSS v4 |
| Database | SQLite (local/production), migrations via Eloquent |
| Testing | Pest v4, PHPStan level 9, Rector (PHP 8.5 target) |
| Auth | Laravel Socialite (GitHub OAuth for admin) |
| Deployment | Docker with PHP-FPM + Nginx, `serversideup/php:8.5-fpm-nginx` |

## Project Structure

```
app/
├── Actions/                 # Business logic (RegisterParticipant, SubscribeToNewsletter, SubmitTestimonial)
├── Checks/                  # Spatie Health checks (Mail, Queue, SevenIo SMS)
├── Console/Commands/        # SendEventReminders, GenerateSitemap
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

**Event Registration:** `EventController::register()` → `EventRegistrationRequest` → `RegisterParticipantAction` → creates/finds Participant → creates Registration → sends confirmation email + optional SMS → clears response cache.

**Newsletter:** `NewsletterController::subscribe()` → `SubscribeToNewsletterAction` → creates subscription with random token → sends welcome email. Sending: `SendNewsletter` Filament page → `SendNewsletterJob` (chunked in 100s).

**Event Reminders:** Scheduled command `events:send-reminders` runs daily at 10:00 → finds events in 24h window → sends email + SMS via `EventNotificationService`.

## Routes

All routes are in `routes/web.php`. No API-specific route file.

| Route | Controller | Purpose |
|-------|-----------|---------|
| `GET /` | PageController::home | Homepage with dynamic content blocks |
| `GET /event` | EventController::showNext | Redirect to next upcoming event |
| `GET /event/{slug}` | EventController::show | Event detail page |
| `POST /event/register` | EventController::register | JSON registration endpoint |
| `GET /teile-deine-erfahrung` | TestimonialSubmissionController::show | Testimonial form |
| `POST /testimonial/submit` | TestimonialSubmissionController::submit | Submit testimonial |
| `POST /newsletter/subscribe` | NewsletterController::subscribe | JSON subscription endpoint |
| `GET /newsletter/unsubscribe/{token}` | NewsletterController::unsubscribe | Token-based unsubscribe |
| `GET /auth/{provider}/redirect` | SocialiteController::redirect | GitHub OAuth (admin) |
| `GET /auth/{provider}/callback` | SocialiteController::callback | OAuth callback |
| `GET /llms.txt` | LlmsController::show | LLM-friendly site description |
| `GET /{slug}` | PageController::show | Dynamic CMS pages (catch-all, last) |

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
│   ├── Controllers/
│   │   ├── EventControllerTest.php          # 10 tests
│   │   └── NewsletterControllerTest.php     # 8 tests
│   ├── Mail/
│   │   └── AdminEventRegistrationNotificationTest.php  # 3 tests
│   ├── QueueHealthCheckTest.php             # 4 tests
│   └── HealthCheckNotificationThrottleTest.php  # 8 tests
└── Unit/Models/
    ├── EventTest.php        # 15 tests
    ├── ParticipantTest.php  # 12 tests
    └── RegistrationTest.php # 9 tests
```

### Factory States

| Factory | Key States |
|---------|-----------|
| Event | `published()`, `unpublished()`, `tomorrow()`, `past()`, `onDate()` |
| Registration | `registered()`, `waitlist()`, `cancelled()`, `attended()`, `forEvent()`, `forParticipant()` |
| Participant | `withPhone()` |
| Page | `published()`, `unpublished()` |
| ContentBlock | `hero()`, `text()`, `forPage()` |
| Newsletter | `draft()`, `sending()`, `sent()` |
| NewsletterSubscription | `active()`, `unconfirmed()`, `unsubscribed()`, `forParticipant()` |
| Testimonial | `unpublished()`, `anonymous()` |

## Code Quality Commands

```bash
# Format PHP code (run before committing)
vendor/bin/pint --dirty

# Static analysis (level 9)
vendor/bin/phpstan analyse

# Rector dry-run (PHP 8.5 modernization)
vendor/bin/rector process --dry-run

# Frontend lint + format
bun run lint
bun run format
bun run typecheck
```

## Frontend Architecture

The frontend is **server-rendered Blade** with progressive enhancement via vanilla TypeScript. Not using Inertia, React, or Vue.

### CSS

Custom component-based CSS in `resources/css/` with OKLCH color palette. Files organized as:
- `base/` — variables (OKLCH colors, spacing, typography), reset, typography
- `components/` — buttons, header, footer, cards, accordion, forms, modal, toast
- `sections/` — hero, intro, moderator, journey, testimonials, faq, newsletter, cta, event
- `utilities/` — layout, animations, visual, view-transitions, print

Tailwind CSS v4 is also available via the Vite plugin for utility classes.

### TypeScript

Entry point: `resources/js/app.ts`. Uses composable pattern:
- `composables/` — `useIntersectionObserver()`, `useParallax()`, `useForm()`, `showToast()`
- `components/` — navigation, forms (newsletter/registration/testimonial), accordion, calendar, faq, animations
- `utils/` — helpers, Umami analytics tracking

### Build

```bash
bun run dev    # Development server (HMR on port 5173)
bun run build  # Production build to public/build/
```

## Content Block System

Pages use a dynamic block system. Supported types in `ContentBlock.type`:
`hero`, `text_section`, `intro`, `value_items`, `journey_steps`, `moderator`, `cta`, `newsletter`, `whatsapp_community`, `testimonials`, `faq`

Each block has a corresponding Blade component in `resources/views/components/blocks/`.

## Configuration

### Key Custom Config Files

| File | Purpose |
|------|---------|
| `config/sevenio.php` | Seven.io SMS API settings |
| `config/analytics.php` | Umami analytics (disabled by default) |
| `config/health.php` | Spatie Health email notifications |
| `config/responsecache.php` | Response cache (7-day TTL) |
| `config/octane.php` | FrankenPHP worker configuration |

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

| Package | Purpose |
|---------|---------|
| `spatie/laravel-responsecache` | Full HTTP response caching |
| `spatie/laravel-medialibrary` | Event images, content block media |
| `spatie/laravel-settings` | Persistent `GeneralSettings` (site name, contact, socials) |
| `spatie/laravel-sluggable` | Auto-slug for Events and Pages |
| `spatie/laravel-health` | Health checks (mail, queue, SMS, disk, DB) |
| `spatie/laravel-sitemap` | Sitemap generation |
| `seven.io/api` | SMS notifications for event reminders |
| `sentry/sentry-laravel` | Error tracking |
| `voku/html-min` | HTML response compression |

## Conventions

- All user-facing text is in German (labels, validation messages, emails, enums)
- Enum keys are TitleCase; enum labels return German translations via `getLabel()`
- Business logic belongs in `Actions/` classes, not controllers
- Models use the `ClearsResponseCache` trait to auto-invalidate cache
- Form validation uses dedicated `FormRequest` classes with German error messages
- Soft deletes on: Event, Registration, NewsletterSubscription, Testimonial, Page, ContentBlock
- View composers inject `$settings` (GeneralSettings) and `$hasNextEvent` to all views

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5.3
- filament/filament (FILAMENT) - v5
- laravel/framework (LARAVEL) - v12
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
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `pest-testing` — Tests applications using the Pest 4 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, browser testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.
- `medialibrary-development` — Build and work with spatie/laravel-medialibrary features including associating files with Eloquent models, defining media collections and conversions, generating responsive images, and retrieving media URLs and paths.
- `responsecache-development` — Cache entire HTTP responses using spatie/laravel-responsecache, including standard caching, flexible (stale-while-revalidate) caching, cache profiles, replacers, and selective cache clearing.

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

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

=== filament/filament rules ===

## Filament

- Filament is used by this application. Follow existing conventions for how and where it's implemented.
- Filament is a Server-Driven UI (SDUI) framework for Laravel that lets you define user interfaces in PHP using structured configuration objects. Built on Livewire, Alpine.js, and Tailwind CSS.
- Use the `search-docs` tool for official documentation on Artisan commands, code examples, testing, relationships, and idiomatic practices.

### Artisan

- Use Filament-specific Artisan commands to create files. Find them with `list-artisan-commands` or `php artisan --help`.
- Inspect required options and always pass `--no-interaction`.

### Patterns

Use static `make()` methods to initialize components. Most configuration methods accept a `Closure` for dynamic values.

Use `Get $get` to read other form field values for conditional logic:

<code-snippet name="Conditional form field" lang="php">
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

<code-snippet name="Computed table column" lang="php">
use Filament\Tables\Columns\TextColumn;

TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),

</code-snippet>

Actions encapsulate a button with optional modal form and logic:

<code-snippet name="Action with modal form" lang="php">
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

Action::make('updateEmail')
    ->form([
        TextInput::make('email')->email()->required(),
    ])
    ->action(fn (array $data, User $record): void => $record->update($data)),

</code-snippet>

### Testing

Authenticate before testing panel functionality. Filament uses Livewire, so use `livewire()` or `Livewire::test()`:

<code-snippet name="Filament Table Test" lang="php">
    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->searchTable($users->first()->name)
        ->assertCanSeeTableRecords($users->take(1))
        ->assertCanNotSeeTableRecords($users->skip(1));

</code-snippet>

<code-snippet name="Filament Create Resource Test" lang="php">
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

<code-snippet name="Testing Validation" lang="php">
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

<code-snippet name="Calling Actions" lang="php">
    use Filament\Actions\DeleteAction;
    use Filament\Actions\Testing\TestAction;

    livewire(EditUser::class, ['record' => $user->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    livewire(ListUsers::class)
        ->callAction(TestAction::make('promote')->table($user), [
            'role' => 'admin',
        ])
        ->assertNotified();

</code-snippet>

### Common Mistakes

**Commonly Incorrect Namespaces:**
- Form fields (TextInput, Select, etc.): `Filament\Forms\Components\`
- Infolist entries (for read-only views) (TextEntry, IconEntry, etc.): `Filament\Infolists\Components\`
- Layout components (Grid, Section, Fieldset, Tabs, Wizard, etc.): `Filament\Schemas\Components\`
- Schema utilities (Get, Set, etc.): `Filament\Schemas\Components\Utilities\`
- Actions: `Filament\Actions\` (no `Filament\Tables\Actions\` etc.)
- Icons: `Filament\Support\Icons\Heroicon` enum (e.g., `Heroicon::PencilSquare`)

**Recent breaking changes to Filament:**
- File visibility is `private` by default. Use `->visibility('public')` for public access.
- `Grid`, `Section`, and `Fieldset` no longer span all columns by default.

=== spatie/laravel-medialibrary rules ===

## Media Library

- `spatie/laravel-medialibrary` associates files with Eloquent models, with support for collections, conversions, and responsive images.
- Always activate the `medialibrary-development` skill when working with media uploads, conversions, collections, responsive images, or any code that uses the `HasMedia` interface or `InteractsWithMedia` trait.

</laravel-boost-guidelines>
