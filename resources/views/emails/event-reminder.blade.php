<x-mail::message>
# Erinnerung: {{ $event->title }} ist morgen!

Hallo {{ $registration->first_name }},

dies ist eine freundliche Erinnerung, dass deine Veranstaltung **{{ $event->title }}** morgen stattfindet.

## Veranstaltungsdetails

**Datum:** {{ $event->event_date->format('d.m.Y') }}<br>
**Uhrzeit:** {{ $event->start_time->format('H:i') }} – {{ $event->end_time->format('H:i') }} Uhr<br>
**Ort:** {{ $event->location }}

@if($event->location_details)
**Genauer Treffpunkt:** {!! nl2br(e($event->location_details)) !!}
@endif

## Zur Erinnerung

{!! nl2br(e($event->description)) !!}

**Teilnahme:** {{ $event->cost_basis }}

## Wichtige Hinweise

- Bitte komme pünktlich zum angegebenen Zeitpunkt
- Bringe eine offene Haltung und Bereitschaft für echte Begegnung mit
- Bei Fragen oder falls du kurzfristig doch nicht teilnehmen kannst, melde dich bitte umgehend per E-Mail bei uns

## Fragen oder Absage?

Falls du kurzfristig nicht teilnehmen kannst oder Fragen hast, schreib uns bitte so schnell wie möglich an [{{ $socialLinks['contact_email'] }}](mailto:{{ $socialLinks['contact_email'] }}).

---

Wir freuen uns auf dich!

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
Diese Erinnerung wurde an {{ $registration->email }} gesendet, weil du für diese Veranstaltung angemeldet bist.
</x-mail::subcopy>
</x-mail::message>
