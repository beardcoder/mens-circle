@props ([
    'page',
    'testimonials' => null,
])

@foreach ($page->contentBlocks as $block)
  @switch ($block->type)
    @case ('hero')
      <x-blocks.hero :block="$block" :page="$page" />
    @break

    @case ('moderator')
      <x-blocks.moderator :block="$block" :page="$page" />
    @break

    @case ('testimonials')
      @if ($testimonials && $testimonials->isNotEmpty())
        <x-blocks.testimonials :testimonials="$testimonials" />
      @endif
    @break

    @case ('whatsapp_community')
      <x-blocks.whatsapp-community />
    @break

    @default
      @php
          $component = 'blocks.' . str_replace('_', '-', $block->type);
      @endphp

      @if (view()->exists('components.' . $component))
        <x-dynamic-component :component="$component" :block="$block" />
      @endif
  @endswitch
@endforeach
