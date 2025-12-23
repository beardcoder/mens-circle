@props(['icon' => null, 'type' => null, 'url' => '#', 'label' => ''])

@php
use App\Enums\Heroicon as AppHeroicon;
use App\Enums\SocialLinkType;

$socialType = null;
if ($type instanceof SocialLinkType) {
    $socialType = $type;
} elseif (is_string($type)) {
    $socialType = SocialLinkType::tryFrom($type);
}

$iconKey = is_string($icon) ? $icon : null;
$heroicon = null;
if ($icon instanceof AppHeroicon) {
    $heroicon = $icon;
} elseif (is_string($iconKey)) {
    $heroicon = AppHeroicon::fromName($iconKey);
}

$iconSvg = $heroicon?->getSvg(24) ?? $socialType?->getIcon() ?? AppHeroicon::LINK->getSvg(24);
$title = $label;
if ($title === '') {
    $title = $socialType?->getLabel()
        ?? $heroicon?->getLabel()
        ?? 'Link';
}

// Prepare URL with mailto: or tel: prefix if needed
$href = $url;
$isInternal = false;
if (str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
    $isInternal = true;
} else {
    $isEmail = filter_var($href, FILTER_VALIDATE_EMAIL) !== false;
    $isPhone = preg_match('/^\+?[0-9\s\-\(\)]+$/', $href) === 1;
    $looksLikeEmailIcon = $iconKey && str_contains($iconKey, 'envelope');
    $looksLikePhoneIcon = $iconKey && str_contains($iconKey, 'phone');

    if ($socialType === SocialLinkType::EMAIL || $isEmail || $looksLikeEmailIcon) {
        $href = 'mailto:' . $href;
        $isInternal = true;
    } elseif ($socialType === SocialLinkType::PHONE || $isPhone || $looksLikePhoneIcon) {
        $href = 'tel:' . str_replace([' ', '-', '(', ')'], '', $href);
        $isInternal = true;
    }
}
@endphp

<a href="{{ $href }}" title="{{ $title }}" target="{{ $isInternal ? '_self' : '_blank' }}" rel="{{ $isInternal ? '' : 'noopener noreferrer' }}" {{ $attributes->merge(['class' => 'social-icon']) }}>
    <span class="social-icon__svg">{!! $iconSvg !!}</span>
    <span class="sr-only">{{ $title }}</span>
</a>
