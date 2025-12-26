@props([
    'src' => '',
    'alt' => '',
    'class' => '',
    'loading' => 'lazy',
    'width' => null,
    'sizes' => null,
])

@php
    $isExternal = $src && str_starts_with($src, 'http');
    $imagePath = $src;

    if (!$isExternal && $src) {
        if (!str_starts_with($src, '/')) {
            $imagePath = 'storage/' . $src;
        } else {
            $imagePath = ltrim($src, '/');
        }
    }
@endphp

@if($src)
    @if($isExternal)
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            @if($class) class="{{ $class }}" @endif
            loading="{{ $loading }}"
            {{ $attributes->except(['src', 'alt', 'loading', 'width', 'sizes']) }}
        />
    @else
        <img
            {{ glide()->src($imagePath, $width, sizes: $sizes, lazy: $loading === 'lazy') }}
            alt="{{ $alt }}"
            @if($class) class="{{ $class }}" @endif
            {{ $attributes->except(['src', 'alt', 'loading', 'width', 'sizes']) }}
        />
    @endif
@endif
