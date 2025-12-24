<x-mail::message>
# Deine Anmeldung ist bestätigt!

Hallo {{ $registration->first_name }},

herzlich willkommen! Wir freuen uns sehr, dass du beim **{{ $event->title }}** dabei sein wirst.

## Veranstaltungsdetails

**Datum:** {{ $event->event_date->format('d.m.Y') }}<br>
**Uhrzeit:** {{ $event->start_time->format('H:i') }} – {{ $event->end_time->format('H:i') }} Uhr<br>
**Ort:** {{ $event->location }}<br>
@if($event->getFullAddress())
**Adresse:** {{ $event->getFullAddress() }}<br>
@endif
@if($event->location_details)
**Hinweise:** {{ $event->location_details }}<br>
@endif

**Tipp:** Im Anhang dieser E-Mail findest du eine iCal-Datei, mit der du den Termin direkt in deinen Kalender eintragen kannst.

## Was dich erwartet

{!! nl2br(e($event->description)) !!}

**Teilnahme:** {{ $event->cost_basis }}

## Vorbereitung

- Bitte komme pünktlich zum angegebenen Zeitpunkt
- Bringe eine offene Haltung und Bereitschaft für echte Begegnung mit
- Bei Fragen oder falls du doch nicht teilnehmen kannst, melde dich gerne per E-Mail bei uns

## Fragen?

Falls du Fragen hast oder aus wichtigen Gründen doch nicht teilnehmen kannst, schreib uns einfach eine E-Mail an [hallo@mens-circle.de](mailto:hallo@mens-circle.de).

---

Wir freuen uns auf dich!

Herzliche Grüße,<br>
**{{ config('app.name') }}**

<x-mail::subcopy>
Diese E-Mail wurde an {{ $registration->email }} gesendet, weil du dich für unsere Veranstaltung angemeldet hast.
</x-mail::subcopy>
</x-mail::message>
