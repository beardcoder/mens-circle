@props([
    'src' => '',
    'alt' => '',
    'class' => '',
    'loading' => 'lazy',
    'width' => null,
    'height' => null,
])

@php
$imageService = app(\App\Services\ImageService::class);
$extraAttributes = $attributes->getAttributes();
@endphp

{!! $imageService->responsiveImage($src, $alt, $class, $loading, $width, $height, $extraAttributes) !!}
