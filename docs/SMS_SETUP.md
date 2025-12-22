# SMS-Benachrichtigungen aktivieren

Die Event-Reminder-Funktion ist bereits für SMS-Unterstützung vorbereitet. Um SMS-Benachrichtigungen zu aktivieren, folge diesen Schritten:

## 1. Vonage (ehemals Nexmo) einrichten

### Installation

```bash
composer require laravel/vonage-notification-channel
```

### Konfiguration

Füge in `.env` deine Vonage-Credentials hinzu:

```env
VONAGE_KEY=your-vonage-key
VONAGE_SECRET=your-vonage-secret
VONAGE_SMS_FROM=491234567890  # Deine Absendernummer
```

## 2. Datenbank aktualisieren

Die Migration für `phone_number` ist bereits erstellt. Führe sie aus:

```bash
php artisan migrate
```

## 3. Registrierungsformular erweitern

Füge ein Telefonnummer-Feld zum Event-Registrierungsformular hinzu:

**Frontend (resources/views/...)**
```html
<input type="tel" name="phone_number" placeholder="Telefonnummer (optional für SMS-Erinnerung)">
```

**Controller (app/Http/Controllers/EventController.php)**
```php
$validated = $request->validate([
    // ... existing fields
    'phone_number' => ['nullable', 'string', 'max:20'],
]);
```

## 4. Notification aktivieren

In `app/Notifications/EventReminderNotification.php`:

1. Füge den Import hinzu:
```php
use Illuminate\Notifications\Messages\VonageMessage;
```

2. Aktiviere den SMS-Channel:
```php
public function via(object $notifiable): array
{
    // Wenn Telefonnummer vorhanden ist, auch SMS senden
    $channels = ['mail'];

    if (!empty($notifiable->phone_number)) {
        $channels[] = 'vonage';
    }

    return $channels;
}
```

3. Uncomment die `toVonage()` Methode:
```php
public function toVonage(object $notifiable): VonageMessage
{
    return (new VonageMessage)
        ->content('Erinnerung: ' . $this->event->title . ' findet morgen um ' .
                  $this->event->start_time->format('H:i') . ' Uhr statt. Ort: ' .
                  $this->event->location);
}
```

## 5. Command anpassen (optional)

Wenn du direkt über das Model notifizieren möchtest statt `Notification::route()`:

**app/Console/Commands/SendEventReminders.php**
```php
// Statt:
Notification::route('mail', $registration->email)
    ->notify(new EventReminderNotification($registration, $event));

// Verwende:
$registration->notify(new EventReminderNotification($registration, $event));
```

## 6. DSGVO-Hinweise

Vergiss nicht, in deiner Datenschutzerklärung zu erwähnen:
- SMS-Benachrichtigungen sind optional
- Telefonnummern werden nur für Event-Erinnerungen verwendet
- Nutzer können sich jederzeit abmelden

## Alternative: Twilio

Statt Vonage kannst du auch Twilio verwenden:

```bash
composer require twilio/sdk
```

Dann in `app/Notifications/EventReminderNotification.php` den Channel auf `'twilio'` ändern und eine `toTwilio()` Methode implementieren.

## Kosten

**Vonage:** Ca. 0,06-0,10 € pro SMS (abhängig vom Zielland)
**Twilio:** Ca. 0,075 € pro SMS nach Deutschland

Beide Anbieter bieten Test-Credits für die Entwicklung.
