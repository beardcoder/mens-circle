@props(['item'])

@php
  $url = $item->computed_url;
  $target = $item->target ?? '_self';
  $cssClass = $item->css_class ?? '';
  $dataAttrs = $item->data_attributes_string ?? '';
  $hasChildren = $item->activeChildren->isNotEmpty();
@endphp

<a
  href="{{ $url }}"
  @if ($target !== '_self') target="{{ $target }}" @endif
  @if ($target === '_blank') rel="noopener noreferrer" @endif
  @if ($cssClass) class="{{ $cssClass }}" @endif
  {!! $dataAttrs !!}
  {{ $attributes }}
>
  @if ($item->icon)
    <span class="nav-icon {{ $item->icon }}"></span>
  @endif
  {{ $item->label }}
</a>

@if ($hasChildren)
  <ul class="nav-submenu">
    @foreach ($item->activeChildren as $child)
      <li>
        <x-navigation-item :item="$child" />
      </li>
    @endforeach
  </ul>
@endif
