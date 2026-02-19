# Changelog

## Aggressive Simplification (Ralph Loop)

### Removed Layers

- **`app/Actions/` (entire directory)** — `RegisterParticipantAction` and `SubscribeToNewsletterAction` had exactly 1 call-site each. Both inlined into their respective controllers.
- **`app/Services/` (entire directory)** — `EventNotificationService` inlined into callers: email sending directly in `EventController`, SMS logic as a private `sendSms()` in both `EventController` and `SendEventReminders`. 15 lines of SMS code duplicated — acceptable to eliminate the service.
- **`app/Filament/Forms/` (entire directory)** — `ParticipantForms::participantSelect()` used in 2 places, inlined into `RegistrationResource` and `RegistrationsRelationManager`.
- **`app/Http/Controllers/Controller.php`** — Empty abstract base class. Laravel 12 does not require controllers to extend anything. Removed from all 6 controllers.

### New Simpler Flow

```
Request → FormRequest (validation) → Controller (all logic inline) → Eloquent → Response
```

- No Action layer between request and model
- No Service layer between controller and notifications
- Controllers own their entire flow: participant upsert, registration restore/create, email queueing, SMS

### Breaking Changes

- `RegisterParticipantAction` deleted (was only used by EventController)
- `SubscribeToNewsletterAction` deleted (was only used by NewsletterController)
- `EventNotificationService` deleted (inlined into EventController + SendEventReminders)
- `App\Filament\Forms\ParticipantForms` deleted
- `App\Http\Controllers\Controller` deleted

---

## Simplification Refactor

### Removed

- **`app/Actions/SubmitTestimonialAction.php`** — Trivial wrapper around `Testimonial::create()`. Logic inlined into `TestimonialSubmissionController::submit()`.
- **`app/Http/Resources/EventResource.php`** — Unused API resource class. No routes, controllers, or views referenced it.
- `Participant::findByEmail()` and its 2 unit tests — Dead production code. No callers outside the tests that verified the method itself.
- `Event::sendRegistrationConfirmation()` and `Event::sendEventReminder()` — Unnecessary delegation methods. Models should not proxy to services. Callers updated to inject `EventNotificationService` directly.
- `ResponseCache::clear()` in `RegisterParticipantAction::execute()` — Redundant. Both `Registration` and `Participant` models use the `ClearsResponseCache` trait which already clears the cache on model create/update/delete.

### Changed

- **`RegisterParticipantAction`** — Now injects `EventNotificationService` via constructor instead of calling it through the `Event` model.
- **`SendEventReminders` command** — Now injects `EventNotificationService` via constructor instead of calling it through the `Event` model.
- **`EventNotificationService`** — Removed noisy `Log::info` success lines (email sent, admin notified). Error logs kept. Inlined the private `sendRegistrationSms()` method (had a single caller). Removed unused `$response` variable from `sendSms()`.
- **`Participant::isSubscribedToNewsletter()`** — Simplified from direct `$subscription->unsubscribed_at === null` check to `$this->newsletterSubscription?->isActive() ?? false`, using the existing `isActive()` method on `NewsletterSubscription`.
- **`AppServiceProvider`** — Removed empty `register()` method (inherited no-op from base class).
- **`SendNewsletterJob`** — Replaced `$failedRecipients` string array (used only for count and empty-check) with a simple `$failedCount` integer. Removed email address leak from error log context.
- **`TestimonialSubmissionController::buildSuccessMessage()`** — Removed redundant `!is_string()` guard (already covered by `empty()`).
- Removed self-describing inline comments from `GenerateSitemap`, `SendEventReminders`, and `SendNewsletterJob`.

### Architecture after simplification

```
Request → FormRequest (validation) → Controller → Action (non-trivial logic only) → Model
                                                 ↘ EventNotificationService (email + SMS)
```

- **Services**: Only `EventNotificationService` remains — it coordinates multi-channel notifications (email + SMS) and is legitimately complex.
- **Actions**: `RegisterParticipantAction` (find-or-update participant, restore/create registration) and `SubscribeToNewsletterAction` (find-or-create participant, restore/resubscribe subscription) — both contain real business logic warranting separation.
- **No repository layer, no DTO layer, no over-abstracted query builders** — direct Eloquent usage throughout.
