<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center" style="padding: 36px 32px 44px 32px;">
@php
    $social_links = app(\App\Settings\GeneralSettings::class)->social_links ?? [];
@endphp
@if(!empty($social_links))
<table cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 24px;">
<tr>
@foreach($social_links as $link)
@php
    $socialType = isset($link['type']) && is_string($link['type'])
        ? \App\Enums\SocialLinkType::tryFrom($link['type'])
        : \App\Enums\SocialLinkType::Other;

    $href = $link['value'];
    if (!str_starts_with($href, 'mailto:') && !str_starts_with($href, 'tel:')) {
        $isEmail = filter_var($href, FILTER_VALIDATE_EMAIL) !== false;
        $isPhone = preg_match('/^\+?[0-9\s\-\(\)]+$/', $href) === 1;

        if ($socialType === \App\Enums\SocialLinkType::Email || $isEmail) {
            $href = 'mailto:' . $href;
        } elseif ($socialType === \App\Enums\SocialLinkType::Phone || $isPhone) {
            $href = 'tel:' . str_replace([' ', '-', '(', ')'], '', $href);
        }
    }

    $title = $link['label'] ?? $socialType?->getLabel() ?? 'Link';
    $iconSvg = $socialType?->getIcon(22) ?? \App\Enums\SocialLinkType::Other->getIcon(22);
@endphp
<td style="padding: 0 8px;">
<a href="{{ $href }}" title="{{ $title }}" style="display: inline-block; width: 22px; height: 22px; color: #7a6248;">
{!! $iconSvg !!}
</a>
</td>
@endforeach
</tr>
</table>
@endif
<table cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 18px;">
<tr>
<td style="padding: 0 6px;">
<span style="color: #c4b49a; font-size: 5px;">&#9679;</span>
</td>
<td style="padding: 0 6px;">
<span style="color: #c4b49a; font-size: 5px;">&#9679;</span>
</td>
<td style="padding: 0 6px;">
<span style="color: #c4b49a; font-size: 5px;">&#9679;</span>
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
