@props (['name', 'size' => null, 'title' => null])

{{--
  Renders a single icon by reference to the inline SVG sprite mounted in
  the layout. Outputs only `<svg><use/></svg>` — two DOM nodes per icon
  regardless of path complexity.

  Named `<x-sprite>` (not `<x-icon>`) to avoid collision with the
  `blade-ui-kit/blade-icons` component shipped by Filament.

  Usage:
    <x-sprite name="play" />
    <x-sprite name="calendar" :size="18" class="event-info__icon" />
    <x-sprite name="logo" title="Männerkreis" />
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
