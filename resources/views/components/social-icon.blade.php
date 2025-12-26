@props(['type' => null, 'url' => '#', 'label' => '', 'variant' => 'icon'])

@php
use App\Enums\SocialLinkType;

$socialType = $type instanceof SocialLinkType ? $type : SocialLinkType::tryFrom($type ?? '') ?? SocialLinkType::OTHER;
$iconSvg = $socialType->getIcon(24);
$title = $label ?: $socialType->getLabel();

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

$isTextVariant = $variant === 'link';
$linkClass = $isTextVariant ? 'social-link' : 'social-icon';
$iconClass = $isTextVariant ? 'social-link__icon' : 'social-icon__svg';

$trackingType = $socialType === SocialLinkType::EMAIL ? 'email' : ($socialType === SocialLinkType::PHONE ? 'phone' : 'social');
$medamaClick = "action=social_click;element=link;type={$trackingType};location=footer";
@endphp

<a href="{{ $href }}" title="{{ $title }}" target="{{ $isInternal ? '_self' : '_blank' }}" rel="{{ $isInternal ? '' : 'noopener noreferrer' }}" data-m:click="{{ $medamaClick }}" {{ $attributes->merge(['class' => $linkClass]) }}>
    <span class="{{ $iconClass }}">{!! $iconSvg !!}</span>
    @if($isTextVariant)
        <span class="social-link__label">{{ $title }}</span>
    @else
        <span class="sr-only">{{ $title }}</span>
    @endif
</a>
