<x-mail::message>
<p style="text-align: center; margin: 0 0 6px; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #b86f52; text-transform: uppercase; letter-spacing: 0.2em;">Willkommen</p>

# Schön, dass du dabei bist.

<p style="text-align: center; color: #5c4a3a; font-size: 15px; margin-bottom: 32px;">Du erhältst ab sofort unseren Newsletter – und bist als Erster informiert, wenn neue Termine und Impulse erscheinen.</p>

## Was dich erwartet

- **Neue Termine** – Erfahre als Erster von kommenden Treffen
- **Inspirierende Impulse** – Gedanken zu Männlichkeit, Gemeinschaft und persönlichem Wachstum
- **Besondere Einladungen** – Exklusive Angebote für die Community

<x-mail::button url="{{ route('event.show') }}?utm_source=email&utm_medium=newsletter&utm_campaign=welcome">
Nächste Termine ansehen
</x-mail::button>

> Der Männerkreis ist ein Raum für echte Begegnung – authentisch, offen und im Austausch miteinander.

---

Wir freuen uns, dass du dabei bist.

Herzliche Grüße,<br>
**{{ config('app.name') }}**

<x-mail::subcopy>
Du erhältst diese E-Mail an {{ $subscription->participant->email }}, weil du dich für unseren Newsletter angemeldet hast.
Falls du den Newsletter nicht mehr erhalten möchtest, kannst du dich jederzeit [hier abmelden]({{ route('newsletter.unsubscribe', $subscription->token) }}?utm_source=email&utm_medium=newsletter&utm_campaign=welcome_unsubscribe).
</x-mail::subcopy>

<x-analytics.email-pixel :subscriptionId="$subscription->id" eventName="newsletter_welcome_open" />
</x-mail::message>
