<x-mail::message>
# Deine Anmeldung ist bestätigt

Hallo {{ $registration->participant->first_name }},

herzlich willkommen! Dein Platz beim **{{ $event->title }}** ist reserviert – wir freuen uns sehr, dich dabei zu haben.

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 28px 0; border-radius: 0; overflow: hidden;">
<tr>
<td style="background-color: #2c2418; padding: 16px 24px;">
<p style="margin: 0; font-family: Georgia, 'Times New Roman', serif; font-size: 14px; font-weight: 400; color: #c4b49a; text-transform: uppercase; letter-spacing: 0.08em;">Veranstaltungsdetails</p>
</td>
</tr>
<tr>
<td style="background-color: #f4f0e8; border: 1px solid #e5ded0; border-top: none; padding: 24px;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="padding: 6px 0; border-bottom: 1px solid #e5ded0;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td width="110" style="font-family: 'DM Sans', sans-serif; font-size: 13px; color: #7a6248; text-transform: uppercase; letter-spacing: 0.05em; padding-right: 16px; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">Datum</td>
<td style="font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418; font-weight: 600; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">{{ $event->event_date->format('l, d. F Y') }}</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="padding: 0; border-bottom: 1px solid #e5ded0;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td width="110" style="font-family: 'DM Sans', sans-serif; font-size: 13px; color: #7a6248; text-transform: uppercase; letter-spacing: 0.05em; padding-right: 16px; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">Uhrzeit</td>
<td style="font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418; font-weight: 600; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">{{ $event->start_time->format('H:i') }} – {{ $event->end_time->format('H:i') }} Uhr</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="padding: 0;@if($event->fullAddress || $event->location_details) border-bottom: 1px solid #e5ded0;@endif">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td width="110" style="font-family: 'DM Sans', sans-serif; font-size: 13px; color: #7a6248; text-transform: uppercase; letter-spacing: 0.05em; padding-right: 16px; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">Ort</td>
<td style="font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418; font-weight: 600; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">{{ $event->location }}</td>
</tr>
</table>
</td>
</tr>
@if($event->fullAddress)
<tr>
<td style="padding: 0;@if($event->location_details) border-bottom: 1px solid #e5ded0;@endif">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td width="110" style="font-family: 'DM Sans', sans-serif; font-size: 13px; color: #7a6248; text-transform: uppercase; letter-spacing: 0.05em; padding-right: 16px; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">Adresse</td>
<td style="font-family: 'DM Sans', sans-serif; font-size: 15px; color: #3a342c; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">{{ $event->fullAddress }}</td>
</tr>
</table>
</td>
</tr>
@endif
@if($event->location_details)
<tr>
<td style="padding: 0;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td width="110" style="font-family: 'DM Sans', sans-serif; font-size: 13px; color: #7a6248; text-transform: uppercase; letter-spacing: 0.05em; padding-right: 16px; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">Hinweis</td>
<td style="font-family: 'DM Sans', sans-serif; font-size: 15px; color: #3a342c; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">{{ $event->location_details }}</td>
</tr>
</table>
</td>
</tr>
@endif
</table>
</td>
</tr>
</table>

Den Termin kannst du direkt in deinen Kalender eintragen – die iCal-Datei findest du im Anhang dieser E-Mail.

## Was dich erwartet

{!! nl2br(e($event->description)) !!}

**Teilnahme:** {{ $event->cost_basis }}

## Damit du gut vorbereitet bist

- Komm pünktlich zum angegebenen Zeitpunkt
- Bring eine offene Haltung und die Bereitschaft für echte Begegnung mit
- Bequeme Kleidung ist von Vorteil

## Fragen?

Schreib uns jederzeit an [hallo@mens-circle.de](mailto:hallo@mens-circle.de) – wir sind gerne für dich da.

---

Wir freuen uns auf dich!

Herzliche Grüße,<br>
**{{ config('app.name') }}**

<x-mail::subcopy>
Diese E-Mail wurde an {{ $registration->participant->email }} gesendet, weil du dich für unsere Veranstaltung angemeldet hast.
</x-mail::subcopy>

<x-analytics.email-pixel :subscriptionId="$registration->id" eventName="event_registration_open" />
</x-mail::message>
