@props ([
    'layers' => 5,
])

<div {{ $attributes->merge(['class' => 'breathing-circles']) }} aria-hidden="true">
  @for ($i = 1; $i <= $layers; $i++)
    <div class="breathing-circle breathing-circle--{{ $i }}"></div>
  @endfor
</div>
