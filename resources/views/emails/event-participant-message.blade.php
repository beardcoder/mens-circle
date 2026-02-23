<x-mail::message>
{!! $mailContent !!}

---

Herzliche Grüße,<br>
**{{ config('app.name') }}**

<x-mail::subcopy>
Diese E-Mail wurde gesendet, weil du für die Veranstaltung „{{ $event->title }}" angemeldet bist.
</x-mail::subcopy>
</x-mail::message>
