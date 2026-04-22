<x-mail::message>
  <p style="text-align: center; margin: 0 0 6px; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; color: #b86f52; text-transform: uppercase; letter-spacing: 0.2em">Erinnerung</p>

  Hallo {{ $registration->participant->first_name }},

  am {{ $event->event_date->translatedFormat('l, d. F Y') }} findet unser nächster Männerkreis statt:

  ---

  **{{ $event->title }}**

  📅 {{ $event->event_date->translatedFormat('l, d. F Y') }}, {{ $event->start_time->format('H:i') }} – {{ $event->end_time->format('H:i') }} Uhr
  📍 {{ $event->location }}

  ---

  Der Männerkreis ist ein Raum für Männer, die nicht nur funktionieren wollen. Ein Abend, an dem du ankommen darfst. Ohne Rolle. Ohne Fassade. Ohne irgendetwas beweisen zu müssen.

  Wir kommen zusammen, um ehrlich zu werden, in den Körper zu kommen und uns mit dem zu verbinden, was im Alltag oft untergeht: Klarheit, Ruhe, Kraft und echter Austausch unter Männern.

  Egal, ob du zum ersten Mal dabei bist oder schon länger Teil des Kreises bist: Du bist willkommen.

  Die Teilnahme ist auf Spendenbasis. Als Orientierung empfehlen wir {{ $event->cost_basis }}.

  Es sind nur noch wenige Plätze frei. Wenn du dabei sein möchtest, melde dich am besten direkt an:

  <x-mail::button :url="route('event.show.slug', $event->slug)">
    Jetzt anmelden
  </x-mail::button>

  Ich freue mich, wenn du dabei bist.

  Herzliche Grüße,
  Markus

  <x-mail::subcopy>
    Diese Erinnerung wurde an {{ $registration->participant->email }} gesendet,
    weil du für diese Veranstaltung angemeldet bist.
  </x-mail::subcopy>

  <x-analytics.email-pixel
    :subscriptionId="$registration->id"
    eventName="event_reminder_open"
  />
</x-mail::message>
