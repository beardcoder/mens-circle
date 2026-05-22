@props ([
    'block',
    'page' => null,
])

@php
    $data = $block->data;
    $anchor = $data['anchor'] ?? null;
    $media = $block->getFieldMedia('image');
    $align = ($data['align'] ?? 'center') === 'left' ? 'left' : 'center';
    $hasImage = $media !== null;
    $hasButton = !empty($data['button_text']) && !empty($data['button_link']);

    $button = null;
    if ($hasButton) {
        $button = App\Support\CmsButtonLink::resolve(
            $data['button_link'],
            $hasNextEvent ?? false,
            $nextEventUrl ?? route('event.show'),
        );
        $hasButton = $button->shouldShow;
        $buttonExternal = $button !== null && str_starts_with($button->href, 'http');
    }
@endphp

<section
  class="page-hero page-hero--{{ $align }} @if ($hasImage) page-hero--with-image @endif"
  @if ($anchor) id="{{ $anchor }}" @endif
>
  <div class="page-hero__ornaments" aria-hidden="true">
    <span class="page-hero__ornament page-hero__ornament--1"></span>
    <span class="page-hero__ornament page-hero__ornament--2"></span>
  </div>

  <div class="container">
    <div class="page-hero__layout">
      <div class="page-hero__content">
        @if (!empty($data['eyebrow']))
          <p class="eyebrow page-hero__eyebrow">{{ $data['eyebrow'] }}</p>
        @endif

        @if (!empty($data['title']))
          <h1 class="page-hero__title">{!! $data['title'] !!}</h1>
        @endif

        @if (!empty($data['lead']))
          <p class="page-hero__lead">{{ $data['lead'] }}</p>
        @endif

        @if ($hasButton)
          <div class="page-hero__cta">
            <a
              href="{{ $button->href }}"
              class="btn btn--primary btn--large"
              @if ($buttonExternal) target="_blank" rel="noopener noreferrer" @endif
            >
              {{ $data['button_text'] }}
            </a>
          </div>
        @endif
      </div>

      @if ($hasImage)
        <div class="page-hero__media">
          {{ $media->img()->attributes([
                    'class' => 'page-hero__image',
                    'loading' => 'eager',
                    'fetchpriority' => 'high',
                    'aria-hidden' => 'true',
                ]) }}
        </div>
      @endif
    </div>
  </div>
</section>
