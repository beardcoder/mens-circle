@props(['navigation', 'cssClass' => ''])

@if ($navigation && $navigation->is_active)
  @php
    $items = $navigation->activeItems()->rootItems()->get();
  @endphp

  @if ($items->isNotEmpty())
    <nav class="{{ $cssClass }}" {{ $attributes }}>
      @foreach ($items as $item)
        <x-navigation-item :item="$item" />
      @endforeach
    </nav>
  @endif
@endif
