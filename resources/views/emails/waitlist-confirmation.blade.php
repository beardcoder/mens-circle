<x-mail::message>
<p style="text-align: center; margin: 0 0 6px; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #b86f52; text-transform: uppercase; letter-spacing: 0.2em;">Warteliste</p>

# Du bist auf der Warteliste!

<p style="text-align: center; color: #5c4a3a; font-size: 15px; margin-bottom: 32px;">Hallo {{ $registration->participant->first_name }}, du bist auf der Warteliste für <strong>{{ $event->title }}</strong>.<br>Wir benachrichtigen dich sofort, wenn ein Platz frei wird.</p>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 0 36px;">
<tr>
<td style="height: 2px; background-color: #c4b49a; font-size: 0; line-height: 0;">&nbsp;</td>
</tr>
<tr>
<td style="background-color: #f4f0e8; padding: 28px;">
<p style="margin: 0 0 20px; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #b86f52; text-transform: uppercase; letter-spacing: 0.15em;">Veranstaltung</p>
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
</table>
</td>
</tr>
</table>

## Was jetzt?

Wir informieren dich automatisch per E-Mail, sobald ein Platz frei wird. Du musst nichts weiter tun.

Solltest du doch nicht mehr teilnehmen können, schreib uns kurz an [hallo@mens-circle.de](mailto:hallo@mens-circle.de), damit wir deinen Platz weitergeben können.

---

Wir freuen uns, dich vielleicht bald begrüßen zu dürfen.

Herzliche Grüße,<br>
**{{ config('app.name') }}**

<x-mail::subcopy>
Diese E-Mail wurde an {{ $registration->participant->email }} gesendet, weil du dich auf die Warteliste für unsere Veranstaltung eingetragen hast.
</x-mail::subcopy>
</x-mail::message>
