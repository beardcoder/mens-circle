<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center" style="padding: 32px 32px 40px 32px;">
@php
    $social_links = app_settings()['social_links'] ?? [];
@endphp
@if(!empty($social_links))
<table cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 20px;">
<tr>
@foreach($social_links as $link)
@php
    $socialType = isset($link['type']) && is_string($link['type'])
        ? \App\Enums\SocialLinkType::tryFrom($link['type'])
        : \App\Enums\SocialLinkType::OTHER;

    $href = $link['value'];
    if (!str_starts_with($href, 'mailto:') && !str_starts_with($href, 'tel:')) {
        $isEmail = filter_var($href, FILTER_VALIDATE_EMAIL) !== false;
        $isPhone = preg_match('/^\+?[0-9\s\-\(\)]+$/', $href) === 1;

        if ($socialType === \App\Enums\SocialLinkType::EMAIL || $isEmail) {
            $href = 'mailto:' . $href;
        } elseif ($socialType === \App\Enums\SocialLinkType::PHONE || $isPhone) {
            $href = 'tel:' . str_replace([' ', '-', '(', ')'], '', $href);
        }
    }

    $title = $link['label'] ?? $socialType?->getLabel() ?? 'Link';
    $iconSvg = $socialType?->getIcon(24) ?? \App\Enums\SocialLinkType::OTHER->getIcon(24);
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
<span style="color: #c4b49a; font-size: 8px;">&#9679;</span>
</td>
<td style="padding: 0 8px;">
<span style="color: #c4b49a; font-size: 8px;">&#9679;</span>
</td>
<td style="padding: 0 8px;">
<span style="color: #c4b49a; font-size: 8px;">&#9679;</span>
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
