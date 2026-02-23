<x-mail::message>
{!! $processedContent !!}

---

Herzliche Grüße,<br>
**{{ config('app.name') }}**

<x-mail::subcopy>
Du erhältst diese E-Mail, weil du dich für den Newsletter von {{ config('app.name') }} angemeldet hast.
[Vom Newsletter abmelden]({{ route('newsletter.unsubscribe', ['token' => $subscription->token]) }}?utm_source=email&utm_medium=newsletter&utm_campaign=unsubscribe)
</x-mail::subcopy>

<x-analytics.email-pixel :subscriptionId="$subscription->id" eventName="newsletter_open" />
</x-mail::message>
