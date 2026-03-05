@props(['media'])

<picture>
    @if($media->hasGeneratedConversion('webp'))
        <source srcset="{{ $media->getUrl('webp') }}" type="image/webp">
    @endif
    <img
        src="{{ $media->getUrl() }}"
        {{ $attributes->merge(['alt' => $media->name]) }}
    >
</picture>
