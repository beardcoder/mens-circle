<x-mail::message>
# Willkommen beim Männerkreis Straubing!

Hallo und herzlich willkommen,

schön, dass du dich für den **Männerkreis Straubing Newsletter** angemeldet hast!

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

Der Männerkreis Straubing ist ein Raum für echte Begegnung. Hier können Männer in einer vertrauensvollen Atmosphäre authentisch sein, sich austauschen und gemeinsam wachsen.

---

Wir freuen uns, dass du dabei bist!

Herzliche Grüße,<br>
**{{ config('app.name') }}**

---

**Bleib in Verbindung:**

@if($socialLinks['website_url'])
🌐 [Webseite]({{ $socialLinks['website_url'] }})
@endif
@if($socialLinks['whatsapp_url'])
📱 [WhatsApp]({{ $socialLinks['whatsapp_url'] }})
@endif
@if($socialLinks['github_url'])
💻 [GitHub]({{ $socialLinks['github_url'] }})
@endif
📧 [{{ $socialLinks['contact_email'] }}](mailto:{{ $socialLinks['contact_email'] }})

<x-mail::subcopy>
Du erhältst diese E-Mail an {{ $subscription->email }}, weil du dich für unseren Newsletter angemeldet hast.
Falls du den Newsletter nicht mehr erhalten möchtest, kannst du dich jederzeit [hier abmelden]({{ route('newsletter.unsubscribe', $subscription->token) }}).
</x-mail::subcopy>
</x-mail::message>
