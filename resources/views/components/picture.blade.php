@props (['media'])

@php
    $width = $media->getCustomProperty('width') ?? $media->width ?? null;
    $height = $media->getCustomProperty('height') ?? $media->height ?? null;
@endphp

<picture>
  @if ($media->hasGeneratedConversion('webp'))
    <source srcset="{{ $media->getUrl('webp') }}" type="image/webp" />
  @endif
  <img
    src="{{ $media->getUrl() }}"
    {{ $attributes->merge(['alt' => $media->name]) }}
    @if ($width && $height)
      width="{{ $width }}"
      height="{{ $height }}"
    @endif
  />
</picture>
