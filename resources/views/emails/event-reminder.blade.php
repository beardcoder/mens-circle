<x-mail::message>
<p style="text-align: center; margin: 0 0 6px; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #b86f52; text-transform: uppercase; letter-spacing: 0.2em;">Erinnerung</p>

# Morgen ist es soweit!

<p style="text-align: center; color: #5c4a3a; font-size: 15px; margin-bottom: 32px;">Hallo {{ $registration->participant->first_name }}, dein Termin<br><strong>{{ $event->title }}</strong> findet morgen statt.</p>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 0 36px;">
<tr>
<td style="height: 2px; background-color: #c4b49a; font-size: 0; line-height: 0;">&nbsp;</td>
</tr>
<tr>
<td style="background-color: #f4f0e8; padding: 28px;">
<p style="margin: 0 0 20px; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #b86f52; text-transform: uppercase; letter-spacing: 0.15em;">Dein Termin</p>
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td width="90" style="padding: 6px 0; vertical-align: top; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #7a6248; text-transform: uppercase; letter-spacing: 0.08em;">Datum</td>
<td style="padding: 6px 0; font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418;">{{ $event->event_date->translatedFormat('l, d. F Y') }}</td>
</tr>
<tr>
<td width="90" style="padding: 6px 0; vertical-align: top; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #7a6248; text-transform: uppercase; letter-spacing: 0.08em;">Uhrzeit</td>
<td style="padding: 6px 0; font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418;">{{ $event->start_time->format('H:i') }} – {{ $event->end_time->format('H:i') }} Uhr</td>
</tr>
<tr>
<td width="90" style="padding: 6px 0; vertical-align: top; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #7a6248; text-transform: uppercase; letter-spacing: 0.08em;">Ort</td>
<td style="padding: 6px 0; font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418;">{{ $event->location }}</td>
</tr>
@if($event->location_details)
<tr>
<td width="90" style="padding: 6px 0; vertical-align: top; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #7a6248; text-transform: uppercase; letter-spacing: 0.08em;">Treffpunkt</td>
<td style="padding: 6px 0; font-family: 'DM Sans', sans-serif; font-size: 15px; color: #3a342c;">{!! nl2br(e($event->location_details)) !!}</td>
</tr>
@endif
</table>
</td>
</tr>
</table>

## Zur Erinnerung

{!! nl2br(e($event->description)) !!}

**Teilnahme:** {{ $event->cost_basis }}

## Bitte beachten

- Komm pünktlich – wir starten gemeinsam
- Bring eine offene Haltung und Bereitschaft für echte Begegnung mit
- Kurzfristig verhindert? Schreib uns bitte an [hallo@mens-circle.de](mailto:hallo@mens-circle.de)

---

Bis morgen!

Herzliche Grüße,<br>
**{{ config('app.name') }}**

<x-mail::subcopy>
Diese Erinnerung wurde an {{ $registration->participant->email }} gesendet, weil du für diese Veranstaltung angemeldet bist.
</x-mail::subcopy>

<x-analytics.email-pixel :subscriptionId="$registration->id" eventName="event_reminder_open" />
</x-mail::message>
