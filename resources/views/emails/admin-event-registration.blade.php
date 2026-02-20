<x-mail::message>
<p style="text-align: center; margin: 0 0 6px; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #b86f52; text-transform: uppercase; letter-spacing: 0.2em;">Neue Anmeldung</p>

# {{ $event->title }}

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 28px 0;">
<tr>
<td style="height: 2px; background-color: #c4b49a; font-size: 0; line-height: 0;">&nbsp;</td>
</tr>
<tr>
<td style="background-color: #f4f0e8; padding: 28px;">
<p style="margin: 0 0 20px; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #b86f52; text-transform: uppercase; letter-spacing: 0.15em;">Teilnehmer</p>
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td width="80" style="padding: 5px 0; vertical-align: top; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #7a6248; text-transform: uppercase; letter-spacing: 0.08em;">Name</td>
<td style="padding: 5px 0; font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418;">{{ $registration->participant->fullName }}</td>
</tr>
<tr>
<td width="80" style="padding: 5px 0; vertical-align: top; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #7a6248; text-transform: uppercase; letter-spacing: 0.08em;">E-Mail</td>
<td style="padding: 5px 0; font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418;">{{ $registration->participant->email }}</td>
</tr>
@if($registration->participant->phone)
<tr>
<td width="80" style="padding: 5px 0; vertical-align: top; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #7a6248; text-transform: uppercase; letter-spacing: 0.08em;">Telefon</td>
<td style="padding: 5px 0; font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418;">{{ $registration->participant->phone }}</td>
</tr>
@endif
</table>
</td>
</tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 0 28px;">
<tr>
<td style="background-color: #f4f0e8; padding: 28px;">
<p style="margin: 0 0 20px; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #b86f52; text-transform: uppercase; letter-spacing: 0.15em;">Veranstaltung</p>
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td width="80" style="padding: 5px 0; vertical-align: top; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #7a6248; text-transform: uppercase; letter-spacing: 0.08em;">Datum</td>
<td style="padding: 5px 0; font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418;">{{ $event->event_date->format('d.m.Y') }}</td>
</tr>
<tr>
<td width="80" style="padding: 5px 0; vertical-align: top; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #7a6248; text-transform: uppercase; letter-spacing: 0.08em;">Uhrzeit</td>
<td style="padding: 5px 0; font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418;">{{ $event->start_time->format('H:i') }} – {{ $event->end_time->format('H:i') }} Uhr</td>
</tr>
<tr>
<td width="80" style="padding: 5px 0; vertical-align: top; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #7a6248; text-transform: uppercase; letter-spacing: 0.08em;">Ort</td>
<td style="padding: 5px 0; font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418;">{{ $event->location }}</td>
</tr>
<tr>
<td width="80" style="padding: 5px 0; vertical-align: top; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #7a6248; text-transform: uppercase; letter-spacing: 0.08em;">Plätze</td>
<td style="padding: 5px 0; font-family: 'DM Sans', sans-serif; font-size: 15px; color: #2c2418; font-weight: 600;">{{ $registrationCount }} / {{ $event->max_participants }}</td>
</tr>
</table>
</td>
</tr>
</table>

<x-mail::subcopy>
Diese E-Mail wurde automatisch versendet.
</x-mail::subcopy>
</x-mail::message>
