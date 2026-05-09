@props (['type' => null, 'url' => '#', 'label' => '', 'variant' => 'icon'])

@php
use App\Enums\SocialLinkType;

$socialType = $type instanceof SocialLinkType ? $type : SocialLinkType::tryFrom($type ?? '') ?? SocialLinkType::Other;
$iconSvg = $socialType->getIcon(20);
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
$linkClass = $isTextVariant
    ? 'inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-sm text-[var(--color-sand)] transition-colors hover:border-[var(--color-terracotta-light)] hover:text-[var(--color-terracotta-light)]'
    : 'grid h-10 w-10 place-items-center rounded-full border border-[var(--border)] text-[var(--fg)] transition-colors hover:border-[var(--accent)] hover:text-[var(--accent)]';
@endphp

<a
  href="{{ $href }}"
  title="{{ $title }}"
  target="{{ $isInternal ? '_self' : '_blank' }}"
  rel="{{ $isInternal ? '' : 'noopener noreferrer' }}"
  data-umami-event="social-click"
  data-umami-event-platform="{{ $socialType->value }}"
  {{ $attributes->merge(['class' => $linkClass]) }}
>
  <span class="grid h-5 w-5 place-items-center">{!! $iconSvg !!}</span>
  @if ($isTextVariant)
    <span>{{ $title }}</span>
  @else
    <span class="sr-only">{{ $title }}</span>
  @endif
</a>
