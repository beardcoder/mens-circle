<x-mail::message>
# Morgen ist es soweit!

Hallo {{ $registration->participant->first_name }},

eine kurze Erinnerung: **{{ $event->title }}** findet morgen statt – und du bist dabei. Wir freuen uns auf dich!

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
<td style="padding: 0;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td width="110" style="font-family: 'DM Sans', sans-serif; font-size: 13px; color: #7a6248; text-transform: uppercase; letter-spacing: 0.05em; padding-right: 16px; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">Ort</td>
<td style="font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418; font-weight: 600; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">{{ $event->location }}</td>
</tr>
</table>
</td>
</tr>
@if($event->location_details)
<tr>
<td style="padding: 0; border-top: 1px solid #e5ded0;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td width="110" style="font-family: 'DM Sans', sans-serif; font-size: 13px; color: #7a6248; text-transform: uppercase; letter-spacing: 0.05em; padding-right: 16px; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">Treffpunkt</td>
<td style="font-family: 'DM Sans', sans-serif; font-size: 15px; color: #3a342c; vertical-align: top; padding-top: 8px; padding-bottom: 8px;">{!! nl2br(e($event->location_details)) !!}</td>
</tr>
</table>
</td>
</tr>
@endif
</table>
</td>
</tr>
</table>

## Zur Erinnerung

{!! nl2br(e($event->description)) !!}

**Teilnahme:** {{ $event->cost_basis }}

## Noch ein paar Hinweise

- Komm pünktlich – wir starten gemeinsam
- Bring eine offene Haltung und die Bereitschaft für echte Begegnung mit
- Falls du kurzfristig verhindert bist, melde dich bitte so schnell wie möglich

## Kurzfristig verhindert?

Schreib uns bitte sofort an [hallo@mens-circle.de](mailto:hallo@mens-circle.de), damit wir planen können.

---

Wir freuen uns auf dich – bis morgen!

Herzliche Grüße,<br>
**{{ config('app.name') }}**

<x-mail::subcopy>
Diese Erinnerung wurde an {{ $registration->participant->email }} gesendet, weil du für diese Veranstaltung angemeldet bist.
</x-mail::subcopy>

<x-analytics.email-pixel :subscriptionId="$registration->id" eventName="event_reminder_open" />
</x-mail::message>
