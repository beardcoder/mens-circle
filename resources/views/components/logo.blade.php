@props (['class' => 'logo__icon'])

<x-sprite name="logo" {{ $attributes->merge(['class' => $class]) }} />
