@props(['icon' => null, 'type' => null, 'url' => '#', 'label' => '', 'variant' => 'icon'])

@php
use App\Enums\Heroicon;
use App\Enums\SocialLinkType;

// Resolve social type
$socialType = $type instanceof SocialLinkType ? $type : SocialLinkType::tryFrom($type ?? '');

// Resolve heroicon
$heroicon = $icon instanceof Heroicon ? $icon : (is_string($icon) ? Heroicon::fromName($icon) : null);

// Get SVG and title
$iconSvg = $heroicon?->getSvg(24) ?? $socialType?->getIcon() ?? Heroicon::LINK->getSvg(24);
$title = $label ?: ($socialType?->getLabel() ?? $heroicon?->getLabel() ?? 'Link');

// Determine link type and format URL
$isInternal = str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:');
$href = $url;

if (!$isInternal) {
    $isEmail = filter_var($url, FILTER_VALIDATE_EMAIL);
    $isPhone = preg_match('/^\+?[0-9\s\-\(\)]+$/', $url);

    if ($isEmail) {
        $href = 'mailto:' . $url;
        $isInternal = true;
    } elseif ($isPhone) {
        $href = 'tel:' . preg_replace('/[\s\-\(\)]/', '', $url);
        $isInternal = true;
    }
}

// CSS classes based on variant
$isTextVariant = $variant === 'link';
$linkClass = $isTextVariant ? 'social-link' : 'social-icon';
$iconClass = $isTextVariant ? 'social-link__icon' : 'social-icon__svg';
@endphp

<a href="{{ $href }}" title="{{ $title }}" target="{{ $isInternal ? '_self' : '_blank' }}" rel="{{ $isInternal ? '' : 'noopener noreferrer' }}" {{ $attributes->merge(['class' => $linkClass]) }}>
    <span class="{{ $iconClass }}">{!! $iconSvg !!}</span>
    @if($isTextVariant)
        <span class="social-link__label">{{ $title }}</span>
    @else
        <span class="sr-only">{{ $title }}</span>
    @endif
</a>
