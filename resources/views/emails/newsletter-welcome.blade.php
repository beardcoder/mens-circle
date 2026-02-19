<x-mail::message>
# Willkommen im Männerkreis!

herzlich willkommen – schön, dass du dabei bist.

Du erhältst ab sofort unseren Newsletter und bist als Erster informiert, wenn neue Termine und Impulse erscheinen.

## Was dich erwartet

- **Neue Termine** – Erfahre als Erster von kommenden Treffen
- **Inspirierende Impulse** – Gedanken zu Männlichkeit, Gemeinschaft und persönlichem Wachstum
- **Besondere Einladungen** – Exklusive Angebote für Community-Mitglieder

## Der erste Schritt

Schau dir gerne unsere nächsten Veranstaltungen an:

<x-mail::button url="{{ route('event.show') }}">
Nächste Termine ansehen
</x-mail::button>

## Über den Männerkreis

Der Männerkreis Niederbayern / Straubing ist ein Raum für echte Begegnung. Männer in einer vertrauensvollen Atmosphäre – authentisch, offen und im Austausch miteinander.

---

Wir freuen uns, dass du dabei bist.

Herzliche Grüße,<br>
**{{ config('app.name') }}**

<x-mail::subcopy>
Du erhältst diese E-Mail an {{ $subscription->participant->email }}, weil du dich für unseren Newsletter angemeldet hast.
Falls du den Newsletter nicht mehr erhalten möchtest, kannst du dich jederzeit [hier abmelden]({{ route('newsletter.unsubscribe', $subscription->token) }}).
</x-mail::subcopy>

<x-analytics.email-pixel :subscriptionId="$subscription->id" eventName="newsletter_welcome_open" />
</x-mail::message>
