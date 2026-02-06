<x-mail::message>
# Neue Anmeldung

Für die Veranstaltung **{{ $event->title }}** hat sich ein neuer Teilnehmer angemeldet.

## Teilnehmer

**Name:** {{ $registration->participant->fullName }}<br>
**E-Mail:** {{ $registration->participant->email }}<br>
@if($registration->participant->phone)
**Telefon:** {{ $registration->participant->phone }}<br>
@endif

## Veranstaltung

**Datum:** {{ $event->event_date->format('d.m.Y') }}<br>
**Uhrzeit:** {{ $event->start_time->format('H:i') }} – {{ $event->end_time->format('H:i') }} Uhr<br>
**Ort:** {{ $event->location }}<br>
**Anmeldungen:** {{ $event->activeRegistrations()->count() }} / {{ $event->max_participants }}

---

Diese E-Mail wurde automatisch versendet.
</x-mail::message>
