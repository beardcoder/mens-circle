# Architecture Documentation

**Laravel 12 Men's Circle Application**  
**Architecture:** Feature-Based (Domain-Oriented)  
**Version:** 2.0 (Post-Refactoring)  
**Last Updated:** 2026-01-26

---

## Overview

This application uses a **feature-based architecture** with clear domain boundaries. Each major business capability is organized as a feature with its own models, services, controllers, and tests.

### Design Philosophy

1. **Boring is Good** - Predictable, consistent patterns
2. **Clarity Over Cleverness** - Obvious intent, minimal abstractions
3. **Maintainability First** - Easy to understand, hard to break
4. **Feature Cohesion** - Related code lives together

---

## Directory Structure

```
app/
├── Features/
│   ├── Events/               # Event management & registration
│   │   ├── Domain/
│   │   │   ├── Models/       # Event, Registration
│   │   │   ├── Services/     # EventRegistrationService, EventNotificationService
│   │   │   └── Enums/        # RegistrationStatus
│   │   └── Http/
│   │       ├── Controllers/  # EventController
│   │       └── Requests/     # EventRegistrationRequest
│   │
│   ├── Newsletters/          # Newsletter subscriptions & sending
│   │   ├── Domain/
│   │   │   ├── Models/       # Newsletter, NewsletterSubscription
│   │   │   ├── Services/     # NewsletterSubscriptionService
│   │   │   └── Enums/        # NewsletterStatus
│   │   └── Http/
│   │       ├── Controllers/  # NewsletterController
│   │       └── Requests/     # NewsletterSubscriptionRequest
│   │
│   ├── Testimonials/         # Testimonial submissions
│   │   ├── Domain/
│   │   │   ├── Models/       # Testimonial
│   │   │   └── Services/     # TestimonialService
│   │   └── Http/
│   │       ├── Controllers/  # TestimonialSubmissionController
│   │       └── Requests/     # TestimonialSubmissionRequest
│   │
│   └── Pages/                # CMS pages & content blocks
│       ├── Domain/
│       │   └── Models/       # Page, ContentBlock
│       └── Http/
│           └── Controllers/  # PageController
│
├── Domain/
│   └── Models/               # Shared domain models
│       └── Participant.php   # Used by Events & Newsletters
│
├── Support/
│   └── Traits/               # Reusable traits
│       └── ClearsResponseCache.php
│
├── Filament/                 # Admin panel (FilamentPHP 5)
│   ├── Resources/            # CRUD resources
│   ├── Pages/                # Custom admin pages
│   └── Widgets/              # Dashboard widgets
│
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php    # Base controller
│   │   ├── LlmsController.php
│   │   └── SocialiteController.php
│   └── Middleware/
│
├── Mail/                     # Email classes
├── Jobs/                     # Background jobs
├── Console/Commands/         # Artisan commands
├── Observers/                # Model observers
├── Providers/                # Service providers
├── Enums/                    # Global enums (Heroicon, SocialLinkType)
├── Models/                   # Core models (User)
└── Settings/                 # Application settings
```

---

## Core Patterns

### 1. Feature Structure

Each feature follows this structure:

```
Features/FeatureName/
├── Domain/           # Business logic & data
│   ├── Models/       # Eloquent models
│   ├── Services/     # Business logic services
│   ├── Enums/        # Feature-specific enums
│   └── ValueObjects/ # (Optional) Value objects
└── Http/             # Web layer
    ├── Controllers/  # Thin orchestration controllers
    └── Requests/     # Form request validation
```

### 2. Controllers (Thin)

Controllers **only** do:
- Validate input (via Form Requests)
- Call service methods
- Return responses (views, JSON, redirects)

**Controllers NEVER do:**
- Business logic
- Database queries (except simple reads)
- Email sending
- Complex calculations

**Example:**
```php
class EventController extends Controller
{
    public function __construct(
        private readonly EventRegistrationService $registrationService
    ) {}

    public function register(EventRegistrationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $event = Event::findOrFail($validated['event_id']);

        try {
            $this->registrationService->register($event, $validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Vielen Dank! Deine Anmeldung war erfolgreich.'
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 409);
        }
    }
}
```

### 3. Services (Business Logic)

Services contain **all** business logic:
- Validation rules
- Complex calculations
- Orchestrating multiple models
- Sending notifications
- Handling state transitions

Services use **constructor dependency injection**:
```php
final readonly class EventRegistrationService
{
    public function __construct(
        private EventNotificationService $notificationService
    ) {}

    public function register(Event $event, array $data): Registration
    {
        $this->validateEventAvailability($event);
        
        // Business logic here...
        
        $this->notificationService->sendRegistrationConfirmation($event, $registration);
        
        return $registration;
    }
}
```

**Rules:**
- Services are `final readonly` classes
- All dependencies injected via constructor
- Public methods are the API (well-named, intention-revealing)
- Throw exceptions for business rule violations

### 4. Models (Data + Simple Accessors)

Models handle:
- Eloquent relationships
- Query scopes
- Attribute casters
- Simple computed properties (Attributes)
- Media collections (Spatie Media Library)

**Models NEVER do:**
- Call services
- Send emails/SMS
- Complex business logic
- Use `app()` helper or service locator

**Example:**
```php
class Event extends Model implements HasMedia
{
    use HasSlug, InteractsWithMedia, SoftDeletes;
    
    protected $fillable = ['title', 'event_date', 'max_participants', ...];
    
    // Relationships
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
    
    // Scopes
    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
    
    // Computed properties
    protected function isFull(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->availableSpots <= 0
        );
    }
    
    // ❌ WRONG - Don't do this
    // public function sendNotification() {
    //     app(NotificationService::class)->send($this);
    // }
}
```

### 5. Form Requests (Validation)

All input validation uses **Form Request** classes:

```php
class EventRegistrationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'exists:events,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'privacy' => ['required', 'accepted'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'first_name.required' => 'Bitte gib deinen Vornamen ein.',
            'email.email' => 'Bitte gib eine gültige E-Mail-Adresse ein.',
        ];
    }
}
```

### 6. Dependency Injection

**Always use constructor injection:**

```php
// ✅ CORRECT
class EventController extends Controller
{
    public function __construct(
        private readonly EventRegistrationService $registrationService
    ) {}
}

// ❌ WRONG - Avoid facades in business logic
class EventController extends Controller
{
    public function register()
    {
        Mail::send(...);  // Don't do this
    }
}

// ❌ WRONG - Avoid service locator
class Event extends Model
{
    public function sendEmail()
    {
        app(MailService::class)->send();  // Don't do this
    }
}
```

---

## Feature Domains

### Events Feature

**Purpose:** Event management, registration, and notifications

**Key Models:**
- `Event` - Event details, dates, capacity
- `Registration` - Participant registration records

**Key Services:**
- `EventRegistrationService` - Handle event registrations
- `EventNotificationService` - Send emails/SMS for events

**Responsibilities:**
- Event CRUD (via Filament)
- Public event registration
- Registration confirmations (email + SMS)
- Event reminders
- Capacity management
- iCal generation

---

### Newsletters Feature

**Purpose:** Newsletter subscription management and sending

**Key Models:**
- `Newsletter` - Newsletter content and metadata
- `NewsletterSubscription` - Subscriber records

**Key Services:**
- `NewsletterSubscriptionService` - Manage subscriptions

**Responsibilities:**
- Newsletter subscription/unsubscription
- Newsletter creation (via Filament)
- Mass email sending (via Jobs)
- Subscription management

---

### Testimonials Feature

**Purpose:** Testimonial submission and management

**Key Models:**
- `Testimonial` - User testimonial records

**Key Services:**
- `TestimonialService` - Handle testimonial submissions

**Responsibilities:**
- Public testimonial submission form
- Testimonial moderation (via Filament)
- Display published testimonials

---

### Pages Feature

**Purpose:** CMS for dynamic pages with content blocks

**Key Models:**
- `Page` - Page metadata and settings
- `ContentBlock` - Reusable content blocks (Hero, CTA, FAQ, etc.)

**Responsibilities:**
- Dynamic page rendering
- Block-based content management
- SEO metadata
- Media handling for blocks

---

## Shared Components

### Participant Model

**Location:** `app/Domain/Models/Participant.php`

**Shared by:** Events, Newsletters

**Purpose:** Unified user/participant record

**Why shared?**
- A person can register for events AND subscribe to newsletter
- Prevents duplicate records
- Centralizes contact information

---

## PHP 8.5 Features Used

1. **Strict Types** - `declare(strict_types=1);` in every file
2. **Readonly Classes** - All services are `final readonly`
3. **Constructor Property Promotion** - Concise constructor syntax
4. **Typed Properties** - All properties have types
5. **Return Type Declarations** - All methods have return types
6. **Enums** - Strong typing for status/type fields
7. **Attributes** - `#[Scope]` for query scopes

---

## Integration Points

### Filament Admin

**Purpose:** Admin panel for content management

**Location:** `app/Filament/`

**Integration:**
- Import models from feature directories
- Keep Filament logic thin
- Delegate to domain services when needed

**Example:**
```php
// app/Filament/Resources/EventResource.php
use App\Features\Events\Domain\Models\Event;
```

### Mail

**Location:** `app/Mail/`

**Integration:**
- Mail classes use models from feature directories
- Queued via Laravel's mail queue
- Templates in `resources/views/emails/`

### Jobs

**Location:** `app/Jobs/`

**Integration:**
- Import models and services from features
- Handle background processing (newsletter sending, etc.)

### Commands

**Location:** `app/Console/Commands/`

**Integration:**
- Import models and services
- Use dependency injection in constructors

---

## Testing Strategy (Recommended)

### Feature Tests

Test the full request/response cycle:

```php
// tests/Features/Events/EventRegistrationTest.php
public function test_can_register_for_event()
{
    $event = Event::factory()->create();
    
    $response = $this->postJson('/event/register', [
        'event_id' => $event->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'privacy' => true,
    ]);
    
    $response->assertSuccessful();
    $this->assertDatabaseHas('registrations', [
        'event_id' => $event->id,
    ]);
}
```

### Unit Tests

Test service methods in isolation:

```php
// tests/Unit/Services/EventRegistrationServiceTest.php
public function test_validates_event_is_not_full()
{
    $event = Event::factory()->full()->create();
    $service = app(EventRegistrationService::class);
    
    $this->expectException(\RuntimeException::class);
    $service->validateEventAvailability($event);
}
```

---

## Adding a New Feature

### Step-by-Step Guide

1. **Create Feature Directory Structure**
```bash
mkdir -p app/Features/NewFeature/{Domain/{Models,Services,Enums},Http/{Controllers,Requests}}
```

2. **Create Models** in `Domain/Models/`
   - Use existing models as templates
   - Add relationships, scopes, casters

3. **Create Services** in `Domain/Services/`
   - `final readonly class`
   - Business logic only
   - Constructor injection

4. **Create Controllers** in `Http/Controllers/`
   - Thin orchestration
   - Inject services
   - Return responses

5. **Create Form Requests** in `Http/Requests/`
   - Validation rules
   - Custom messages

6. **Register Routes** in `routes/web.php`
   ```php
   use App\Features\NewFeature\Http\Controllers\NewFeatureController;
   
   Route::controller(NewFeatureController::class)->group(function() {
       Route::get('/new-feature', 'index');
   });
   ```

7. **Create Filament Resources** (if admin needed)
   - Import models from feature directory

8. **Write Tests**
   - Feature tests for HTTP endpoints
   - Unit tests for services

---

## Conventions & Best Practices

### Naming

- **Controllers:** `{Resource}Controller` (e.g., `EventController`)
- **Services:** `{Resource}{Action}Service` (e.g., `EventRegistrationService`)
- **Requests:** `{Resource}{Action}Request` (e.g., `EventRegistrationRequest`)
- **Models:** Singular (e.g., `Event`, not `Events`)

### Code Style

- Follow PSR-12
- Use Laravel Pint for formatting
- 4 spaces indentation
- Strict types always

### Error Handling

- Throw `\RuntimeException` for business rule violations
- Catch exceptions in controllers
- Return user-friendly error messages
- Log errors appropriately

### Comments

- Write self-documenting code (clear names)
- Comment "why", not "what"
- PHPDoc for complex methods
- Type hints over comments

---

## Troubleshooting

### Class Not Found

**Problem:** `Class 'App\Models\Event' not found`

**Solution:** Update import to `App\Features\Events\Domain\Models\Event`

### Service Not Injecting

**Problem:** Service not available in controller

**Solution:** Check constructor, ensure typehint is correct

### Filament Resource Error

**Problem:** Filament resource can't find model

**Solution:** Update `protected static string $model` to new path

---

## Future Improvements (Not Yet Implemented)

1. **DTOs** - Data Transfer Objects for complex data
2. **Value Objects** - Email, Phone number classes
3. **Policies** - Authorization (if needed for multi-tenant)
4. **Repositories** - Only if query complexity increases significantly
5. **API Resources** - If API layer is needed
6. **Events & Listeners** - For decoupled cross-feature communication

**Note:** Don't add these until you have a clear need. Simplicity is a feature.

---

## Resources

- [Laravel Documentation](https://laravel.com/docs/12.x)
- [FilamentPHP Documentation](https://filamentphp.com/docs/5.x)
- [PHP 8.5 Features](https://www.php.net/releases/8.5/en.php)
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)

---

**Maintained by:** Development Team  
**Questions?** Ask in team chat or open a GitHub Discussion
