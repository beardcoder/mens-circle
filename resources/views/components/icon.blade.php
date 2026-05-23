@props (['name', 'size' => null, 'title' => null])

{{--
  Renders a single icon by reference to the inline SVG sprite mounted in
  the layout. Outputs only `<svg><use/></svg>` — two DOM nodes per icon
  regardless of path complexity.

  Usage:
    <x-icon name="play" />
    <x-icon name="calendar" :size="18" class="event-info__icon" />
    <x-icon name="logo" title="Männerkreis" />
--}}

<svg
  @if ($size !== null) width="{{ (int) $size }}" height="{{ (int) $size }}" @endif
  @if ($title) role="img" @else aria-hidden="true" focusable="false" @endif
  {{ $attributes->merge(['class' => 'icon']) }}
>
  @if ($title)
    <title>{{ $title }}</title>
  @endif

  <use href="#icon-{{ $name }}" />
</svg>
