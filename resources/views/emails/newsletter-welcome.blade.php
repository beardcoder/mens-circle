<x-mail::message>
# Willkommen beim Männerkreis Niederbayern/ Straubing!

Hallo und herzlich willkommen,

schön, dass du dich für den **Männerkreis Niederbayern/ Straubing Newsletter** angemeldet hast!

## Was dich erwartet

Mit diesem Newsletter bleibst du auf dem Laufenden über:

- **Kommende Treffen und Veranstaltungen** – Erfahre als Erster von neuen Terminen
- **Inspirierende Impulse** – Gedanken rund um Männlichkeit, Gemeinschaft und persönliches Wachstum
- **Besondere Angebote** – Exklusive Einladungen zu besonderen Events

## Der nächste Schritt

Schau dir gerne unsere nächsten Veranstaltungen an und melde dich für ein Treffen an:

<x-mail::button url="{{ route('event.show') }}">
Nächste Termine ansehen
</x-mail::button>

## Über uns

Der Männerkreis Niederbayern/ Straubing ist ein Raum für echte Begegnung. Hier können Männer in einer vertrauensvollen Atmosphäre authentisch sein, sich austauschen und gemeinsam wachsen.

---

Wir freuen uns, dass du dabei bist!

Herzliche Grüße,<br>
**{{ config('app.name') }}**

<x-mail::subcopy>
Du erhältst diese E-Mail an {{ $subscription->participant->email }}, weil du dich für unseren Newsletter angemeldet hast.
Falls du den Newsletter nicht mehr erhalten möchtest, kannst du dich jederzeit [hier abmelden]({{ route('newsletter.unsubscribe', $subscription->token) }}).
</x-mail::subcopy>

<x-analytics.email-pixel :subscriptionId="$subscription->id" eventName="newsletter_welcome_open" />
</x-mail::message>
