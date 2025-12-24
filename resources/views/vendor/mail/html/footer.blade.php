<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center" style="padding: 32px 32px 40px 32px;">
@php
    $socialLinks = settings()['social_links'] ?? [];
@endphp
@if(!empty($socialLinks))
<table cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 20px;">
<tr>
@foreach($socialLinks as $link)
@php
    $socialType = isset($link['type']) && is_string($link['type'])
        ? \App\Enums\SocialLinkType::tryFrom($link['type'])
        : null;
    $iconKey = $link['icon'] ?? null;
    $heroicon = is_string($iconKey) ? \App\Enums\Heroicon::fromName($iconKey) : null;

    $href = $link['value'];
    if (!str_starts_with($href, 'mailto:') && !str_starts_with($href, 'tel:')) {
        $isEmail = filter_var($href, FILTER_VALIDATE_EMAIL) !== false;
        $isPhone = preg_match('/^\+?[0-9\s\-\(\)]+$/', $href) === 1;
        $looksLikeEmailIcon = is_string($iconKey) && str_contains($iconKey, 'envelope');
        $looksLikePhoneIcon = is_string($iconKey) && str_contains($iconKey, 'phone');

        if ($socialType === \App\Enums\SocialLinkType::EMAIL || $isEmail || $looksLikeEmailIcon) {
            $href = 'mailto:' . $href;
        } elseif ($socialType === \App\Enums\SocialLinkType::PHONE || $isPhone || $looksLikePhoneIcon) {
            $href = 'tel:' . str_replace([' ', '-', '(', ')'], '', $href);
        }
    }

    $title = $link['label']
        ?? $socialType?->getLabel()
        ?? $heroicon?->getLabel()
        ?? 'Link';
    $iconSvg = $heroicon?->getSvg(24)
        ?? $socialType?->getIcon()
        ?? \App\Enums\Heroicon::LINK->getSvg(24);
@endphp
<td style="padding: 0 8px;">
<a href="{{ $href }}" title="{{ $title }}" style="display: inline-block; width: 24px; height: 24px; color: #7a6248;">
{!! $iconSvg !!}
</a>
</td>
@endforeach
</tr>
</table>
@endif
<table cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 16px;">
<tr>
<td style="padding: 0 8px;">
<span style="color: #c4b49a; font-size: 8px;">●</span>
</td>
<td style="padding: 0 8px;">
<span style="color: #c4b49a; font-size: 8px;">●</span>
</td>
<td style="padding: 0 8px;">
<span style="color: #c4b49a; font-size: 8px;">●</span>
</td>
</tr>
</table>
<p style="font-family: 'DM Sans', 'Segoe UI', sans-serif; font-size: 12px; color: #7a6248; margin: 0; line-height: 1.6;">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</p>
</td>
</tr>
</table>
</td>
</tr>
