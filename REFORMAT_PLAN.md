# Laravel 12 Architecture Refactoring Plan

**Date:** 2026-01-26  
**PHP Version:** 8.4+ (Note: Current environment has 8.3.6, production should have 8.4+)  
**Laravel Version:** 12.x  
**Framework:** FilamentPHP 5 (Admin Only), Blade Templates (Frontend)

---

## Executive Summary

This document outlines a comprehensive refactoring plan to transform the current mixed-pattern codebase into a clean, feature-based architecture. The goal is to eliminate architectural drift, reduce complexity, and establish clear boundaries and responsibilities.

---

## Current State Analysis

### Architectural Patterns Identified

The codebase currently uses a **hybrid approach** with multiple patterns coexisting:

1. **Actions Pattern** (`app/Actions/`)
   - `RegisterParticipantAction`
   - `SubmitTestimonialAction`
   - `SubscribeToNewsletterAction`

2. **Services Pattern** (`app/Services/`)
   - `EventNotificationService`

3. **Standard Laravel MVC** (`app/Http/Controllers/`)
   - Controllers with varying levels of logic
   - Some are thin (PageController), others have validation logic

4. **Models with Business Logic** (`app/Models/`)
   - Models contain some business methods (e.g., `Event::sendRegistrationConfirmation()`)
   - Static methods on models (e.g., `Participant::findOrCreateByEmail()`)

### Issues Identified

#### 1. **Pattern Inconsistency**
- **Actions** used for some features but not others
- **Services** exist but underutilized
- No clear rule on when to use Actions vs Services vs Controller methods

#### 2. **Responsibility Boundaries Unclear**
- `Event` model calls `EventNotificationService` directly via `app()` helper
- Actions mix data transformation with business logic
- Controllers contain validation logic inline with business rules

#### 3. **Code Organization**
- Flat structure makes feature discovery difficult
- No domain grouping
- Hard to understand feature scope at a glance

#### 4. **Specific Code Smells**

**In `EventController::register()`:**
```php
// Validation logic mixed with business logic
if (! $event->is_published) { ... }
if ($event->isPast) { ... }
if ($event->isFull) { ... }
```
- Business rules should be in a service, not controller

**In `RegisterParticipantAction`:**
```php
ResponseCache::clear(); // Infrastructure concern
$event->sendRegistrationConfirmation($registration); // Domain concern
```
- Mixed concerns: caching, domain logic

**In `Event` model:**
```php
public function sendRegistrationConfirmation(Registration $registration): void
{
    app(EventNotificationService::class)->sendRegistrationConfirmation($this, $registration);
}
```
- Service locator pattern (anti-pattern)
- Model should not orchestrate services

**In `TestimonialSubmissionController`:**
```php
private function buildSuccessMessage(array $validated): string
{
    // Business logic in controller
}
```
- Should be in service or value object

#### 5. **Missing Abstractions**
- No DTOs (Data Transfer Objects) for complex data structures
- No Value Objects (e.g., email, phone number)
- No Policies for authorization (though may not be needed for public site)

#### 6. **PHP 8.4/8.5 Features Underutilized**
- `readonly` classes not used for DTOs
- Constructor property promotion used, but inconsistently
- Enums present but could be expanded (e.g., for event validation states)

---

## Target Architecture

### Feature-Based Structure

```
app/
├── Features/
│   ├── Events/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── EventController.php
│   │   │   └── Requests/
│   │   │       └── EventRegistrationRequest.php
│   │   ├── Domain/
│   │   │   ├── Models/
│   │   │   │   ├── Event.php
│   │   │   │   └── Registration.php
│   │   │   ├── Services/
│   │   │   │   ├── EventRegistrationService.php
│   │   │   │   └── EventNotificationService.php
│   │   │   ├── Enums/
│   │   │   │   └── RegistrationStatus.php
│   │   │   └── ValueObjects/
│   │   │       └── EventAvailability.php (optional)
│   │   └── Tests/
│   │       ├── EventControllerTest.php
│   │       └── EventRegistrationServiceTest.php
│   │
│   ├── Newsletters/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── NewsletterController.php
│   │   │   └── Requests/
│   │   │       └── NewsletterSubscriptionRequest.php
│   │   ├── Domain/
│   │   │   ├── Models/
│   │   │   │   └── NewsletterSubscription.php
│   │   │   ├── Services/
│   │   │   │   └── NewsletterSubscriptionService.php
│   │   │   └── Enums/
│   │   │       └── NewsletterStatus.php
│   │   └── Tests/
│   │
│   ├── Testimonials/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── TestimonialSubmissionController.php
│   │   │   └── Requests/
│   │   │       └── TestimonialSubmissionRequest.php
│   │   ├── Domain/
│   │   │   ├── Models/
│   │   │   │   └── Testimonial.php
│   │   │   └── Services/
│   │   │       └── TestimonialService.php
│   │   └── Tests/
│   │
│   └── Pages/
│       ├── Http/
│       │   └── Controllers/
│       │       └── PageController.php
│       ├── Domain/
│       │   ├── Models/
│       │   │   ├── Page.php
│       │   │   └── ContentBlock.php
│       │   └── Services/
│       │       └── PageService.php (if needed)
│       └── Tests/
│
├── Domain/
│   ├── Models/
│   │   └── Participant.php (shared across features)
│   └── ValueObjects/ (if needed)
│
├── Filament/
│   ├── Resources/
│   ├── Pages/
│   └── Widgets/
│
├── Support/
│   ├── Traits/
│   │   └── ClearsResponseCache.php
│   └── Mail/ (if generic)
│
└── [Keep existing]
    ├── Console/
    ├── Http/
    │   ├── Middleware/
    │   └── Resources/ (API resources if needed)
    ├── Jobs/
    ├── Providers/
    ├── Enums/ (global enums)
    └── Settings/
```

### Design Principles

1. **Controllers are thin orchestrators**
   - Validate input (via Form Requests)
   - Call domain services
   - Return responses
   - No business logic

2. **Services contain business logic**
   - Single Responsibility Principle
   - Named after business operations (e.g., `EventRegistrationService`)
   - Injected dependencies (no facades or service locators in production code)

3. **Models are data + simple accessors**
   - Eloquent relationships
   - Scopes
   - Attribute casters
   - Simple computed properties
   - NO service calls, NO complex business logic

4. **One pattern, consistently applied**
   - Eliminate Actions pattern, consolidate into Services
   - Services are injected via constructor
   - All business operations go through Services

5. **Clear feature boundaries**
   - Easy to find all code related to a feature
   - Tests co-located with features
   - Reduces cognitive load

---

## Refactoring Strategy

### Phase 1: Create Feature Structure (Non-Breaking)

1. Create new directory structure under `app/Features/`
2. Update `composer.json` autoloader if needed (PSR-4 should handle it)
3. No file moves yet - just setup

### Phase 2: Refactor Events Feature

This is the most complex feature with the most logic.

**Steps:**

1. **Create EventRegistrationService**
   - Merge logic from `RegisterParticipantAction`
   - Add event availability validation
   - Clean separation of concerns
   
2. **Refactor EventNotificationService**
   - Already a service, just move it
   - Ensure it's dependency-injected, not service-located

3. **Move Models**
   - Move `Event` to `Features/Events/Domain/Models/`
   - Move `Registration` to `Features/Events/Domain/Models/`
   - Remove service calls from model (move to service)

4. **Refactor Controller**
   - Move to `Features/Events/Http/Controllers/`
   - Inject `EventRegistrationService`
   - Remove business logic, keep orchestration only

5. **Update Filament Resources**
   - Update import paths
   - Keep Filament logic thin

6. **Add Tests**
   - Feature tests for registration flow
   - Unit tests for service logic

### Phase 3: Refactor Newsletters Feature

Simpler than Events.

**Steps:**

1. **Create NewsletterSubscriptionService**
   - Merge logic from `SubscribeToNewsletterAction`
   
2. **Move Models**
   - Move `NewsletterSubscription` to `Features/Newsletters/Domain/Models/`

3. **Refactor Controller**
   - Move to `Features/Newsletters/Http/Controllers/`
   - Inject service

4. **Update Filament Resources**

5. **Add Tests**

### Phase 4: Refactor Testimonials Feature

Simplest feature.

**Steps:**

1. **Create TestimonialService**
   - Merge logic from `SubmitTestimonialAction`
   - Move message building logic

2. **Move Model**
   - Move to `Features/Testimonials/Domain/Models/`

3. **Refactor Controller**

4. **Update Filament Resources**

5. **Add Tests**

### Phase 5: Refactor Pages Feature

CMS feature.

**Steps:**

1. **Move Models**
   - Move `Page` to `Features/Pages/Domain/Models/`
   - Move `ContentBlock` to `Features/Pages/Domain/Models/`

2. **Controller** (already thin, just move)

3. **Update Filament Resources**

### Phase 6: Shared Components

1. **Participant Model**
   - Move to `app/Domain/Models/`
   - This model is shared across Events and Newsletters
   - Keep it at domain level, not feature-specific

2. **Enums**
   - Keep global enums in `app/Enums/`
   - Move feature-specific enums to feature directories

3. **Traits**
   - Move to `app/Support/Traits/`

4. **Mail Classes**
   - Keep in `app/Mail/` or move to feature-specific if tightly coupled
   - For this app, keeping in `app/Mail/` is fine

### Phase 7: PHP 8.4/8.5 Modernization

Apply modern PHP features:

1. **Readonly DTOs** for service inputs/outputs
2. **Constructor property promotion** everywhere
3. **Strict types** (already present, good!)
4. **Typed properties** (mostly done, ensure consistency)
5. **Enums** for all magic strings/constants

---

## Migration Path (Backward Compatible)

To ensure zero downtime and backward compatibility:

1. **Copy, don't move initially**
   - Create new Services alongside existing Actions
   - Update Controllers to use new Services
   - Keep old Actions temporarily (unused)

2. **Update references progressively**
   - Update one feature at a time
   - Test thoroughly after each feature

3. **Remove old code**
   - Only after new code is tested and verified
   - Delete old Actions
   - Delete old locations

4. **Update Filament last**
   - Filament is admin-only, lower risk
   - Update all imports
   - Test admin flows

---

## Risks & Mitigations

### Risk 1: Breaking Filament Resources
**Mitigation:** Update import paths carefully, test admin panel thoroughly

### Risk 2: Breaking Frontend Routes
**Mitigation:** Routes remain unchanged, only internals refactored

### Risk 3: Missing Edge Cases in Services
**Mitigation:** Comprehensive tests for each service method

### Risk 4: Performance Degradation
**Mitigation:** Profile before/after, ensure no N+1 queries introduced

### Risk 5: Observer/Event Listeners Breaking
**Mitigation:** Review all model observers, ensure they work with new structure

---

## Success Criteria

1. ✅ All routes work identically to before
2. ✅ Filament admin panel functions without issues
3. ✅ No business logic in controllers
4. ✅ Clear feature boundaries
5. ✅ Single pattern (Services) consistently applied
6. ✅ All Actions removed, logic moved to Services
7. ✅ PHPStan level 8 passes
8. ✅ Laravel Pint passes
9. ✅ Feature tests added and passing
10. ✅ Code is simpler, more readable, easier to maintain

---

## Implementation Order

1. **REFORMAT_PLAN.md** (this document) ✅
2. **Events Feature** - Most complex, do first
3. **Newsletters Feature** - Medium complexity
4. **Testimonials Feature** - Simplest
5. **Pages Feature** - CMS, straightforward
6. **Shared Components** - Clean up
7. **Testing & Quality** - Comprehensive tests
8. **Documentation** - Update README, add architecture docs

---

## Files to Move/Refactor

### Events Feature
- `app/Models/Event.php` → `app/Features/Events/Domain/Models/Event.php`
- `app/Models/Registration.php` → `app/Features/Events/Domain/Models/Registration.php`
- `app/Http/Controllers/EventController.php` → `app/Features/Events/Http/Controllers/EventController.php`
- `app/Http/Requests/EventRegistrationRequest.php` → `app/Features/Events/Http/Requests/EventRegistrationRequest.php`
- `app/Actions/RegisterParticipantAction.php` → **DELETE** (logic moves to `EventRegistrationService`)
- `app/Services/EventNotificationService.php` → `app/Features/Events/Domain/Services/EventNotificationService.php`
- **NEW:** `app/Features/Events/Domain/Services/EventRegistrationService.php`
- `app/Enums/RegistrationStatus.php` → `app/Features/Events/Domain/Enums/RegistrationStatus.php`

### Newsletters Feature
- `app/Models/NewsletterSubscription.php` → `app/Features/Newsletters/Domain/Models/NewsletterSubscription.php`
- `app/Http/Controllers/NewsletterController.php` → `app/Features/Newsletters/Http/Controllers/NewsletterController.php`
- `app/Http/Requests/NewsletterSubscriptionRequest.php` → `app/Features/Newsletters/Http/Requests/NewsletterSubscriptionRequest.php`
- `app/Actions/SubscribeToNewsletterAction.php` → **DELETE** (logic moves to `NewsletterSubscriptionService`)
- **NEW:** `app/Features/Newsletters/Domain/Services/NewsletterSubscriptionService.php`
- `app/Enums/NewsletterStatus.php` → `app/Features/Newsletters/Domain/Enums/NewsletterStatus.php`

### Testimonials Feature
- `app/Models/Testimonial.php` → `app/Features/Testimonials/Domain/Models/Testimonial.php`
- `app/Http/Controllers/TestimonialSubmissionController.php` → `app/Features/Testimonials/Http/Controllers/TestimonialSubmissionController.php`
- `app/Http/Requests/TestimonialSubmissionRequest.php` → `app/Features/Testimonials/Http/Requests/TestimonialSubmissionRequest.php`
- `app/Actions/SubmitTestimonialAction.php` → **DELETE** (logic moves to `TestimonialService`)
- **NEW:** `app/Features/Testimonials/Domain/Services/TestimonialService.php`

### Pages Feature
- `app/Models/Page.php` → `app/Features/Pages/Domain/Models/Page.php`
- `app/Models/ContentBlock.php` → `app/Features/Pages/Domain/Models/ContentBlock.php`
- `app/Http/Controllers/PageController.php` → `app/Features/Pages/Http/Controllers/PageController.php`

### Shared/Global
- `app/Models/Participant.php` → `app/Domain/Models/Participant.php`
- `app/Traits/ClearsResponseCache.php` → `app/Support/Traits/ClearsResponseCache.php`
- Keep `app/Mail/*` in place (acceptable location)
- Keep `app/Jobs/*` in place
- Keep `app/Observers/*` in place (update imports)
- Keep `app/Enums/Heroicon.php`, `app/Enums/SocialLinkType.php` (global enums)

### Filament Resources (Update Imports Only)
- All files in `app/Filament/` stay in place
- Update model imports to new locations
- Test admin panel thoroughly

---

## Testing Strategy

### Feature Tests (End-to-End)
- Event registration flow (happy path)
- Event registration validation (full event, past event, unpublished)
- Newsletter subscription (new subscriber)
- Newsletter subscription (already subscribed)
- Newsletter unsubscription
- Testimonial submission
- Page rendering with content blocks

### Unit Tests (Service Logic)
- `EventRegistrationService` methods
- `EventNotificationService` (email and SMS sending, with mocks)
- `NewsletterSubscriptionService` methods
- `TestimonialService` methods

### Integration Tests
- Filament resource operations (create, edit, list)
- Observer triggers
- Job dispatching

---

## Rollback Plan

If refactoring introduces critical issues:

1. **Per-Feature Rollback**
   - Each feature is refactored independently
   - Can revert individual commits without affecting others

2. **Old Code Preservation**
   - Keep old Actions temporarily as "deprecated"
   - Can quickly switch back if needed

3. **Git Strategy**
   - Small, atomic commits per feature
   - Easy to cherry-pick or revert

4. **Database**
   - No schema changes in this refactoring
   - No rollback needed at DB level

---

## Post-Refactoring Improvements (Future)

These are out of scope for this refactoring but noted for future consideration:

1. **Add Policies** for authorization (if needed)
2. **Add DTOs** for complex service inputs
3. **Add Value Objects** for Email, Phone, etc.
4. **Extract Query Builders** for complex queries
5. **Add Repository Pattern** (only if needed, don't over-engineer)
6. **Add Event Sourcing** (only if needed, probably not)
7. **API Layer** (if API is needed in future)

---

## Notes

- **Filament:** Admin panel only, keep isolated, don't mix with frontend logic
- **Blade:** Frontend uses Blade templates, no SPA considerations
- **No breaking changes** to routes or public APIs
- **PHP 8.4+** required (current environment has 8.3.6, will need upgrade)
- **Laravel 12** features can be leveraged as needed
- **No tests currently exist** - need to create comprehensive test suite

---

**Prepared by:** GitHub Copilot Agent (Laravel Specialist)  
**Approved by:** [Pending Review]  
**Status:** Draft - Ready for Implementation
