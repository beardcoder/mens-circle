@props(['type' => 'other', 'url' => '#', 'label' => ''])

@php
use App\Enums\SocialLinkType;

$socialType = is_string($type) ? SocialLinkType::tryFrom($type) : $type;
$socialType = $socialType ?? SocialLinkType::OTHER;

$icon = $socialType->getIcon();
$title = $label ?: $socialType->getLabel();

// Prepare URL with mailto: or tel: prefix if needed
$href = $url;
if ($socialType === SocialLinkType::EMAIL && !str_starts_with($url, 'mailto:')) {
    $href = 'mailto:' . $url;
} elseif ($socialType === SocialLinkType::PHONE && !str_starts_with($url, 'tel:')) {
    $href = 'tel:' . str_replace([' ', '-', '(', ')'], '', $url);
}

$isInternal = in_array($socialType, [SocialLinkType::EMAIL, SocialLinkType::PHONE]);
@endphp

<a href="{{ $href }}" title="{{ $title }}" target="{{ $isInternal ? '_self' : '_blank' }}" rel="{{ $isInternal ? '' : 'noopener noreferrer' }}" {{ $attributes->merge(['class' => 'social-icon']) }}>
    <span class="social-icon__svg">{!! $icon !!}</span>
    <span class="sr-only">{{ $title }}</span>
</a>
