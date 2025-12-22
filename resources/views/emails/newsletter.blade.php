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

---

**Bleib in Verbindung:**

@if($socialLinks['website_url'])
🌐 [Webseite]({{ $socialLinks['website_url'] }})
@endif
@if($socialLinks['whatsapp_url'])
📱 [WhatsApp]({{ $socialLinks['whatsapp_url'] }})
@endif
@if($socialLinks['github_url'])
💻 [GitHub]({{ $socialLinks['github_url'] }})
@endif
📧 [{{ $socialLinks['contact_email'] }}](mailto:{{ $socialLinks['contact_email'] }})
</x-mail::message>
