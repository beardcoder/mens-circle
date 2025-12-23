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
    use App\Enums\SocialLinkType;
    $socialType = SocialLinkType::tryFrom($link['type']) ?? SocialLinkType::OTHER;
    $href = $link['value'];
    if ($socialType === SocialLinkType::EMAIL && !str_starts_with($href, 'mailto:')) {
        $href = 'mailto:' . $href;
    } elseif ($socialType === SocialLinkType::PHONE && !str_starts_with($href, 'tel:')) {
        $href = 'tel:' . str_replace([' ', '-', '(', ')'], '', $href);
    }
    $title = $link['label'] ?: $socialType->getLabel();
@endphp
<td style="padding: 0 8px;">
<a href="{{ $href }}" title="{{ $title }}" style="display: inline-block; width: 24px; height: 24px; color: #7a6248;">
{!! $socialType->getIcon() !!}
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
