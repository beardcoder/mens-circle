@props(['media', 'conversion' => ''])

@php
    $avifSrcset = $media->getSrcset('avif');
    $webpSrcset = $media->getSrcset('webp');
    $fallbackUrl = $media->getUrl($conversion);

    // Single-conversion fallbacks (no responsive images generated yet)
    $webpFallback = (!$webpSrcset && $media->hasGeneratedConversion('webp'))
        ? $media->getUrl('webp')
        : null;
@endphp

<picture>
  @if ($avifSrcset)
    <source srcset="{{ $avifSrcset }}" type="image/avif" />
  @endif
  @if ($webpSrcset)
    <source srcset="{{ $webpSrcset }}" type="image/webp" />
  @elseif ($webpFallback)
    <source srcset="{{ $webpFallback }}" type="image/webp" />
  @endif
  <img
    src="{{ $fallbackUrl }}"
    {{ $attributes->merge(['alt' => $media->name ?? '']) }}
  />
</picture>
