@props(['media', 'conversion' => '', 'sizes' => '100vw'])

@php
    $avifSrcset = $media->getSrcset('avif');
    $webpSrcset = $media->getSrcset('webp');
    $fallbackUrl = $media->getUrl($conversion);

    // Single-conversion fallbacks (no responsive images generated yet)
    $webpFallback = (!$webpSrcset && $media->hasGeneratedConversion('webp'))
        ? $media->getUrl('webp')
        : null;

    // Largest responsive image width for intrinsic dimensions (prevents CLS)
    $responsiveFiles = $media->hasResponsiveImages('webp')
        ? $media->responsiveImages('webp')->files
        : $media->responsiveImages()->files;

    $largest = $responsiveFiles->sortByDesc(fn ($f) => $f->width())->first();
@endphp

<picture>
  @if ($avifSrcset)
    <source srcset="{{ $avifSrcset }}" sizes="{{ $sizes }}" type="image/avif" />
  @endif
  @if ($webpSrcset)
    <source srcset="{{ $webpSrcset }}" sizes="{{ $sizes }}" type="image/webp" />
  @elseif ($webpFallback)
    <source srcset="{{ $webpFallback }}" type="image/webp" />
  @endif
  <img
    src="{{ $fallbackUrl }}"
    @if ($largest)
      width="{{ $largest->width() }}"
      height="{{ $largest->height() }}"
    @endif
    {{ $attributes->merge(['alt' => $media->name ?? '']) }}
  />
</picture>
