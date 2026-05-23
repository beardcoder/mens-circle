@props (['class' => 'logo__icon'])

<x-icon name="logo" {{ $attributes->merge(['class' => $class]) }} />
