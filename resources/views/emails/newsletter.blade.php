<x-mail::message>
{!! nl2br(e($newsletter->content)) !!}

<x-mail::panel>
Du erhältst diese E-Mail, weil du dich für den Newsletter von Männerkreis Straubing angemeldet hast.
</x-mail::panel>

<x-mail::button :url="route('newsletter.unsubscribe', ['token' => $subscription->token])">
Vom Newsletter abmelden
</x-mail::button>

Herzliche Grüße,<br>
{{ config('app.name') }}
</x-mail::message>
