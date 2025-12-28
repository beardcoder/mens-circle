# Men's Circle 2026 - AI Assistant Guide

This document provides comprehensive guidance for AI assistants working on the Men's Circle 2026 (Männerkreis Straubing) application.

---

=== project overview ===

## Project Description

**Men's Circle 2026** is a professional German-language website for managing and organizing men's circle events. The application provides event management, newsletter subscriptions, online registrations, and CMS functionality for the Männerkreis Straubing community.

**Primary Language:** German (de_DE locale)
**Target Audience:** Men's circle participants in Straubing, Germany
**Main Purpose:** Event coordination, community engagement, newsletter distribution

---

=== domain model ===

## Core Data Models

### Event
**Purpose:** Represents men's circle gathering events

**Key Fields:**
- `title` - Event title (German)
- `slug` - Auto-generated from `event_date` in format `YYYY-MM-DD`
- `description` - Rich text event description
- `event_date` - Date of the event (datetime)
- `start_time`, `end_time` - Event duration
- `location` - Venue name
- `location_details` - Additional location information
- `max_participants` - Capacity limit (default: 8)
- `cost_basis` - Pricing information
- `is_published` - Publication status (boolean)

**Relationships:**
- `hasMany(EventRegistration)` - All registrations
- `confirmedRegistrations()` - Only confirmed registrations

**Important Methods:**
- `availableSpots()` - Returns remaining capacity
- `isFull()` - Checks if event is at capacity
- `getSlugOptions()` - Generates slug from event date

**Traits:** `HasSlug`, `SoftDeletes`

### EventRegistration
**Purpose:** Tracks participant registrations for events

**Key Fields:**
- `event_id` - Foreign key to Event
- `first_name`, `last_name`, `email` - Participant details
- `privacy_accepted` - GDPR consent (boolean, required)
- `status` - Registration status (default: 'confirmed')
- `confirmed_at` - Timestamp of confirmation

**Relationships:**
- `belongsTo(Event)`

**Business Logic:**
- Duplicate registrations prevented (same email + event)
- Automatic confirmation emails sent via `EventRegistrationConfirmation` mailable
- Registration fails if event is full

### Page
**Purpose:** CMS for static and dynamic content pages

**Key Fields:**
- `title` - Page title
- `slug` - URL-friendly identifier
- `content_blocks` - JSON array of content blocks
- `meta` - JSON array of SEO metadata
- `is_published` - Publication status
- `published_at` - Publication timestamp

**Traits:** `HasSlug`, `SoftDeletes`

**Special Pages:**
- Impressum (legal imprint)
- Datenschutz (privacy policy)
- Custom content pages

### Newsletter
**Purpose:** Manages newsletter campaigns

**Key Fields:**
- `subject` - Email subject line
- `content` - HTML email content
- `sent_at` - Dispatch timestamp
- `recipient_count` - Number of recipients
- `status` - Campaign status ('draft', 'sending', 'sent')

**Important Methods:**
- `isSent()` - Check if newsletter has been sent
- `isDraft()` - Check if newsletter is still in draft

**Related Job:** `SendNewsletterJob` (queued for background dispatch)

### NewsletterSubscription
**Purpose:** Manages newsletter subscriber list

**Key Fields:**
- `email` - Subscriber email
- `status` - Subscription status ('active', 'unsubscribed')
- `token` - Unique 64-character unsubscribe token
- `subscribed_at` - Subscription timestamp
- `unsubscribed_at` - Unsubscription timestamp

**Boot Method:**
- Automatically generates unique token on creation
- Sets `subscribed_at` to current timestamp

**Mailables:**
- `NewsletterWelcome` - Sent on new subscription
- `NewsletterMail` - Campaign emails

### User
**Purpose:** Admin panel authentication

**Standard Laravel user model for Filament admin access**

---

=== key features & workflows ===

## Event Management Workflow

1. **Event Creation** (Admin Panel)
   - Admin creates event via Filament resource
   - Slug auto-generated from `event_date` (YYYY-MM-DD format)
   - Event can be saved as draft (`is_published = false`)
   - EventObserver handles post-creation tasks

2. **Event Publication**
   - Admin sets `is_published = true`
   - Event appears on public website
   - Cache for `has_next_event` invalidated (600s TTL)

3. **Participant Registration** (Public Website)
   - User submits registration form via AJAX
   - Controller validates: first_name, last_name, email, privacy_accepted
   - System checks for duplicate registration (email + event)
   - System verifies event has available spots
   - Creates EventRegistration with status 'confirmed'
   - Sends confirmation email (EventRegistrationConfirmation)
   - Returns JSON response with success/error

4. **Registration Management** (Admin Panel)
   - View all registrations per event
   - Track confirmed vs total registrations
   - Monitor available spots in real-time

## Newsletter System Workflow

1. **Subscription** (Public Website)
   - User submits email via newsletter form
   - Unique 64-char token generated automatically
   - `subscribed_at` timestamp recorded
   - Welcome email sent (NewsletterWelcome)

2. **Newsletter Creation** (Admin Panel)
   - Admin creates newsletter in Filament
   - Sets subject and HTML content
   - Status defaults to 'draft'

3. **Newsletter Dispatch** (Admin Panel)
   - Admin navigates to custom "Send Newsletter" page
   - Selects newsletter to send
   - System dispatches SendNewsletterJob to queue
   - Job iterates through active subscribers
   - Each email includes unique unsubscribe link
   - Updates newsletter status to 'sent'
   - Records `recipient_count` and `sent_at`

4. **Unsubscribe** (Public Website)
   - User clicks unsubscribe link with token
   - System validates token
   - Updates status to 'unsubscribed'
   - Records `unsubscribed_at` timestamp

## CMS Content Workflow

1. **Page Creation** (Admin Panel)
   - Admin creates page via Filament
   - Slug auto-generated from title
   - Content stored as JSON blocks
   - Meta data for SEO
   - PageObserver handles updates

2. **Page Publication**
   - Set `is_published = true`
   - Page accessible via `/{slug}` route

3. **Special Pages**
   - Impressum accessible at `/impressum`
   - Datenschutz accessible at `/datenschutz`
   - These have dedicated routes and controllers

---

=== frontend architecture ===

## Technology Stack

**CSS Framework:** Tailwind CSS 4.0.0
**JavaScript:** Vanilla JavaScript (no framework)
**Build Tool:** Vite 7.0.7
**Reactive Components:** Livewire 3 (minimal usage)

---

=== css architecture & guidelines ===

## Directory Structure

The CSS follows a modular ITCSS-inspired architecture:

## Design Tokens (CSS Custom Properties)

### Color System

Uses OKLCH color space for perceptually uniform colors:

```css
/* Primitive Colors */
--color-earth-deep: oklch(18% 0.04 55);    /* Deep brown */
--color-terracotta: oklch(52% 0.14 35);    /* Warm terracotta */
--color-sand: oklch(76% 0.04 75);          /* Light sand */
--color-sage: oklch(45% 0.08 145);         /* Muted green */

/* Semantic Tokens (Light Theme) */
--text-primary: var(--color-ink);
--text-secondary: var(--color-ink-soft);
--text-muted: oklch(55% 0.02 68);
--bg-primary: var(--color-bone);
--bg-elevated: var(--bg-primary);
--accent-primary: var(--color-terracotta);
```

### Spacing Scale

Consistent 8px-based spacing scale:

```css
--space-xs: 0.5rem;   /* 8px */
--space-sm: 0.75rem;  /* 12px */
--space-md: 1rem;     /* 16px */
--space-lg: 1.5rem;   /* 24px */
--space-xl: 2rem;     /* 32px */
--space-2xl: 4rem;    /* 64px */
--space-3xl: 6rem;    /* 96px */
```

### Typography Scale

Fluid typography using `clamp()`:

```css
--text-sm: 0.875rem;
--text-base: 1rem;
--text-lg: 1.125rem;
--text-xl: 1.25rem;
--text-2xl: clamp(1.5rem, 3vw, 2rem);
--text-3xl: clamp(2rem, 4vw, 3rem);
--text-4xl: clamp(2.5rem, 6vw, 4.5rem);
```

## Naming Conventions (BEM)

Use BEM (Block Element Modifier) naming:

```css
/* Block */
.testimonial-card { }

/* Element (double underscore) */
.testimonial-card__quote { }
.testimonial-card__author { }

/* Modifier (double dash) */
.testimonial-card--featured { }
.btn--primary { }
.btn--lg { }
```

### Section Naming Pattern

Sections follow a consistent pattern:

```css
/* Base section class */
.newsletter-section { }

/* Layout container */
.newsletter__layout { }

/* Content areas */
.newsletter__content { }
.newsletter__eyebrow { }
.newsletter__title { }
.newsletter__text { }
```

## Modern CSS Features (2025)

This project uses cutting-edge CSS features:

### Logical Properties

Use logical properties for RTL support:

```css
/* ✓ Preferred */
margin-inline-start: var(--space-md);
padding-block: var(--space-lg);
inset-block-start: 0;

/* ✗ Avoid */
margin-left: var(--space-md);
padding-top: var(--space-lg);
top: 0;
```

### Native CSS Nesting

Use CSS nesting for component encapsulation:

```css
.faq__layout {
  display: grid;

  @media (width < 900px) {
    grid-template-columns: 1fr;
  }
}
```

### color-mix() Function

Mix colors dynamically:

```css
background: color-mix(in oklch, var(--accent-primary) 15%, transparent);
border-color: color-mix(in oklch, var(--text-primary) 20%, transparent);
```

### Container Queries

For component-based responsive design (where applicable):

```css
.card {
  container-type: inline-size;
}

@container (width < 300px) {
  .card__content { flex-direction: column; }
}
```

### Subgrid

For aligned nested grids:

```css
.grid-parent {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
}

.grid-child {
  display: grid;
  grid-template-columns: subgrid;
}
```

## Responsive Design

### Mobile-First Breakpoints

Use consolidated breakpoints:

```css
/* Mobile first - no media query needed */
.element { /* Base mobile styles */ }

/* Tablet (600px+) */
@media (width >= 600px) { }

/* Small Desktop (900px+) */
@media (width >= 900px) { }

/* Large Desktop (1200px+) */
@media (width >= 1200px) { }
```

### Fluid Layouts

Prefer fluid sizing over fixed breakpoints:

```css
/* ✓ Preferred - fluid */
font-size: clamp(1.5rem, 3vw, 2.5rem);
padding: clamp(1rem, 3vw, 2rem);

/* ✓ Grid auto-fit for responsive columns */
grid-template-columns: repeat(auto-fit, minmax(min(100%, 350px), 1fr));
```

## Dark Mode

Automatic dark mode via `prefers-color-scheme`:

```css
@media (prefers-color-scheme: dark) {
  :root {
    --bg-primary: var(--color-ink);
    --text-primary: var(--color-parchment);
    /* All semantic tokens redefined */
  }
}
```

**Important:** Use semantic tokens (`--text-primary`, `--bg-elevated`) instead of hardcoded colors so dark mode works automatically.

## Accessibility

### Reduced Motion

Always respect user preferences:

```css
@media (prefers-reduced-motion: reduce) {
  .animated-element {
    animation: none;
    transition: none;
  }
}
```

### Focus States

Visible focus indicators:

```css
.btn:focus-visible {
  outline: 2px solid var(--accent-primary);
  outline-offset: 2px;
}
```

### Screen Reader Only

```css
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  clip: rect(0, 0, 0, 0);
}
```

## Utility Classes

Available utility classes (use sparingly, prefer component styles):

```css
/* Layout */
.container, .flex, .grid, .hidden, .block

/* Spacing */
.mt-md, .mb-lg, .px-sm (margin/padding utilities)

/* Text */
.text-center, .text-primary, .font-display

/* Visual */
.rounded-lg, .shadow-md, .bg-elevated
```

## Best Practices

1. **Use semantic variables** - Never use raw colors, always use `--text-primary`, `--bg-elevated`, etc.

2. **Low specificity selectors** - Avoid deep nesting, prefer flat BEM selectors

3. **Single responsibility** - Each CSS file handles one component/section

4. **Mobile-first** - Write base styles for mobile, add complexity for larger screens

5. **Avoid `!important`** - If needed, refactor the specificity chain

6. **Comment section headers** - Use consistent section comment format:
   ```css
   /* ============================================
      Section Name
      ============================================ */
   ```

7. **Consolidate noise textures** - Use the `.texture-noise` utility class instead of duplicating SVG backgrounds

## Filament Resource Organization

**Pattern:** Each Filament resource has a dedicated directory structure:

```
app/Filament/Resources/
├── Events/
│   ├── EventResource.php          # Main resource class
│   ├── Schemas/
│   │   └── EventForm.php          # Extracted form schema
│   └── Tables/
│       └── EventTable.php         # Extracted table definition
```

**Convention:**
- Extract form schemas to dedicated `Schemas/` directory
- Extract table definitions to `Tables/` directory
- Keep resource class focused on configuration
- Use namespaced classes for better organization

**Example Form Schema:**
```php
namespace App\Filament\Resources\Events\Schemas;

class EventForm
{
    public static function schema(): array
    {
        return [
            // Filament form components
        ];
    }
}
```

## Observer Pattern Usage

**Location:** `app/Observers/`

**Current Observers:**
- `EventObserver` - Handles Event model lifecycle events
- `PageObserver` - Handles Page model lifecycle events

**Registration:** Observers are registered in `AppServiceProvider::boot()`

```php
Page::observe(PageObserver::class);
Event::observe(EventObserver::class);
```

**Purpose:** Cache invalidation, slug generation, related data updates

## View Data Sharing Pattern

**Global Data:** `AppServiceProvider` uses `View::share()` to share data across all views

**Implementation:** Data is shared via `shareGlobalViewData()` method which runs once per request (more efficient than View Composer which runs per view).

**Shared Data:**
- `hasNextEvent` - Boolean indicating if upcoming events exist (cached forever, invalidated via EventObserver)
- `settings` - GeneralSettings object (cached internally by Spatie Laravel Settings)
- Individual setting properties extracted for convenience (`siteName`, `contactEmail`, etc.)

**Caching Strategy:**
- `hasNextEvent`: Forever cache with event-based invalidation via `EventObserver`
- Settings: Cached internally by Spatie Settings (config: `settings.cache.enabled = true`)
- No double caching - relies on framework-level caching

**Purpose:**
- Conditional navigation links (event CTAs)
- Header/footer content (site name, contact info)
- SEO metadata
- Social links

## Slug Generation Strategy

**Events:** Slugs are auto-generated from `event_date` in `YYYY-MM-DD` format
```php
SlugOptions::create()
    ->generateSlugsFrom(fn ($model) => $model->event_date->format('Y-m-d'))
    ->saveSlugsTo('slug');
```

**Pages:** Slugs are auto-generated from `title` (Spatie default behavior)

## Caching Strategy

**Current Cache Usage:**
- `has_next_event` - Forever cache with event-based invalidation (EventObserver)
- `settings.*` - Forever cache via Spatie Settings internal caching
- Driver: Database (configured in `.env`)

**Cache Invalidation:**

**Events Cache (`has_next_event`):**
- Invalidated via `EventObserver` when:
  - Event created
  - Event updated (if `is_published` or `event_date` changed)
  - Event deleted
  - Event restored

**Settings Cache:**
- Invalidated automatically by Spatie Laravel Settings
- `ClearSettingsCache` listener registered but Spatie handles cache clearing
- No manual cache management needed

**Performance Benefits:**
- Forever caching eliminates TTL checks
- Event-based invalidation ensures data freshness
- Single cache key per data type (no cache key proliferation)
- Spatie Settings uses optimized caching internally

## Queue Configuration

**Driver:** Database (default for production)
**Testing:** Sync queue for immediate execution

**Queued Jobs:**
- `SendNewsletterJob` - Dispatches newsletter emails
- Implements `ShouldQueue` interface

**Queue Worker:** Started via `composer run dev` script

---

=== routing conventions ===

## Route Naming

All routes use named routes for URL generation:

**Pattern Routes:**
- `home` - Homepage (/)
- `event.show` - Next upcoming event (/event)
- `event.show.slug` - Specific event by slug (/event/{slug})
- `event.register` - Event registration (POST)
- `newsletter.subscribe` - Newsletter subscription (POST)
- `newsletter.unsubscribe` - Unsubscribe with token (GET)
- `impressum` - Legal imprint (/impressum)
- `datenschutz` - Privacy policy (/datenschutz)
- `page.show` - Dynamic CMS pages (/{slug})

**SEO Routes:**
- `sitemap` - XML sitemap (/sitemap.xml)
- `robots` - Robots.txt (/robots.txt)

**Important:** Always use `route('name')` helper, never hardcode URLs

**Catch-All Route:** `/{slug}` route MUST be last to avoid conflicts

## Controller Methods

**Pattern:** RESTful-style controller methods

```php
// Show resources
public function show(string $slug): View
public function showNext(): View

// Store resources
public function register(Request $request): JsonResponse
public function subscribe(Request $request): JsonResponse

// Update resources
public function unsubscribe(string $token): RedirectResponse
```

**JSON Responses:** Event registration and newsletter subscription return JSON for AJAX handling

---

=== development workflows ===

## Local Development Setup

```bash
# Initial setup
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build

# Quick setup (uses composer script)
composer run setup
```

## Development Server

**Recommended:** Use the all-in-one dev script

```bash
composer run dev
```

**What it does:**
- Starts PHP development server (localhost:8000)
- Starts queue worker (with --tries=1)
- Starts Laravel Pail (log viewer with --timeout=0)
- Starts Vite dev server (HMR enabled)
- Uses `concurrently` to run all in parallel
- Color-coded output for each service
- Kills all processes if one fails

**Individual Commands:**
```bash
php artisan serve          # Server only
php artisan queue:listen   # Queue worker only
php artisan pail          # Logs only
npm run dev               # Vite only
```

## Asset Building

**Development:**
```bash
npm run dev  # Vite with HMR
```

**Production:**
```bash
npm run build  # Optimized bundle
```

**If frontend changes aren't visible:**
- Ask user to run `npm run build` or `composer run dev`
- Check Vite manifest file exists
- Clear browser cache

## Database Migrations

**Running Migrations:**
```bash
php artisan migrate           # Run pending migrations
php artisan migrate:fresh     # Drop all tables and re-migrate
php artisan migrate --seed    # Run with seeders
```

**Creating Migrations:**
```bash
php artisan make:migration create_table_name --create=table_name
php artisan make:migration add_column_to_table --table=table_name
```

## Code Formatting

**CRITICAL:** Always run Pint before committing

```bash
vendor/bin/pint --dirty  # Format only changed files
vendor/bin/pint          # Format all files
```

**Do not use:** `vendor/bin/pint --test` (only checks, doesn't fix)

## Testing

**Run all tests:**
```bash
php artisan test
```

**Run specific test file:**
```bash
php artisan test tests/Feature/EventTest.php
```

**Run filtered tests:**
```bash
php artisan test --filter=testEventRegistration
```

**Test Configuration:**
- Database: In-memory SQLite
- Session: Array driver
- Cache: Array driver
- Queue: Sync driver
- Environment: `.env.testing` (if exists)

---

=== testing strategy ===

## Test Organization

**Location:**
- `tests/Feature/` - Feature tests (HTTP, database, integration)
- `tests/Unit/` - Unit tests (isolated logic)

**Preference:** Most tests should be feature tests

## Test Creation

```bash
# Feature test
php artisan make:test EventRegistrationTest

# Unit test
php artisan make:test EventModelTest --unit
```

## Testing Best Practices

1. **Test all paths:**
   - Happy path (expected behavior)
   - Failure path (validation errors, exceptions)
   - Edge cases (boundary conditions, weird inputs)

2. **Use factories:**
   - Always use model factories in tests
   - Check for custom factory states before manual setup
   - Example: `Event::factory()->published()->create()`

3. **Database handling:**
   - Tests use in-memory SQLite (fast, isolated)
   - Each test runs in transaction (auto-rollback)
   - No need to manually clean database

4. **Testing event registration:**
```php
public function test_user_can_register_for_event(): void
{
    $event = Event::factory()->published()->create();

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy_accepted' => true,
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('event_registrations', [
        'email' => 'max@example.com',
        'event_id' => $event->id,
    ]);
}
```

5. **Testing newsletter subscription:**
```php
public function test_user_can_subscribe_to_newsletter(): void
{
    Mail::fake();

    $response = $this->postJson(route('newsletter.subscribe'), [
        'email' => 'subscriber@example.com',
    ]);

    $response->assertOk();
    Mail::assertSent(NewsletterWelcome::class);
    $this->assertDatabaseHas('newsletter_subscriptions', [
        'email' => 'subscriber@example.com',
        'status' => 'active',
    ]);
}
```

## After Tests Pass

**Workflow:**
1. Run specific test after making changes
2. Verify test passes
3. Ask user if they want to run full test suite
4. Run `vendor/bin/pint --dirty` before committing

---

=== filament admin panel ===

## Admin Panel Access

**URL:** `/admin`
**Authentication:** Standard Laravel authentication with User model

## Custom Filament Pages

**Location:** `app/Filament/Pages/`

**Current Custom Pages:**
- `SendNewsletter` - Custom interface for dispatching newsletter campaigns

**View:** `resources/views/filament/pages/send-newsletter.blade.php`

## Filament Resources

**Pattern:** One resource per model

**Available Resources:**
- `EventResource` - CRUD for events
- `EventRegistrationResource` - View registrations
- `PageResource` - CMS page management
- `NewsletterResource` - Newsletter campaigns
- `NewsletterSubscriptionResource` - Subscriber management

## Filament Configuration

**Location:** `app/Providers/Filament/AdminPanelProvider.php`

**Features:**
- Gravatar integration for user avatars
- Overlook plugin for dashboard widgets
- Custom color scheme (if configured)
- Navigation organization

---

=== important notes for ai assistants ===

## Language Context

**Primary Language:** German (Germany)
- All user-facing content is in German
- Email templates are in German
- Validation messages should be in German
- Admin panel can be English (Filament default)

**When creating content:**
- Use formal German ("Sie" form) for public website
- Use appropriate German formatting (dates, numbers)
- Follow German legal requirements (Impressum, Datenschutz)

## GDPR Compliance

**Requirements:**
- Privacy acceptance required for event registrations
- Unsubscribe links in all marketing emails
- Data retention policies (soft deletes)
- Clear data usage statements

**Implementation:**
- `privacy_accepted` field on EventRegistration (required)
- Unique tokens for newsletter unsubscribe
- Soft deletes on models for data recovery
- Dedicated privacy policy page

## Performance Considerations

**Caching:**
- Use cache for expensive queries
- Set appropriate TTL (e.g., 600s for event queries)
- Invalidate cache when data changes (use observers)

**Eager Loading:**
- Always eager load relationships to prevent N+1
- Example: `Event::with('confirmedRegistrations')->get()`

**Queue Usage:**
- Queue time-consuming operations (email sending)
- Use database queue for production
- Test with sync queue

## Security Best Practices

**Already Implemented:**
- CSRF token validation on all forms
- Email validation
- Soft deletes for data safety
- Unique token generation for unsubscribe

**Always Maintain:**
- Validate all user input
- Use Form Requests for complex validation
- Sanitize output in views (Blade automatic escaping)
- Follow Laravel security best practices

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5.0
- filament/filament (FILAMENT) - v4
- laravel/framework (LARAVEL) - v12
- laravel/mcp (MCP) - v0
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v3
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== livewire/core rules ===

## Livewire Core
- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>


## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>


    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>


=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives
- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
</laravel-boost-guidelines>
