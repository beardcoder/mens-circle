@props ([
    'block',
    'page' => null,
])

@php
    $data = $block->data;
    $media = $block->getFieldMedia('photo');

    if ($media) {
        $media->name = strip_tags($data['name'] ?? 'Moderator');
    }
@endphp

<section class="section moderator-section" id="moderator">
  <div class="container">
    <div class="moderator__layout">
      <div class="moderator__photo-wrapper" data-anim="slide-right">
        <div class="moderator__photo">
          @if ($media)
            {{ $media->img()->attributes([
                            'loading' => 'lazy',
                            'decoding' => 'async',
                        ]) }}
          @else
            <div class="moderator__photo-placeholder">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="8" r="4" />
                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
              </svg>
              <span>Foto</span>
            </div>
          @endif
        </div>
        <div class="moderator__photo-accent"></div>
      </div>

      <div class="moderator__content" data-anim-group>
        @if (!empty($data['eyebrow']))
          <p class="eyebrow" data-anim="rise">{{ $data['eyebrow'] }}</p>
        @endif

        @if (!empty($data['name']))
          <h2 class="moderator__name" data-anim="rise">
            {!! $data['name'] !!}
          </h2>
        @endif

        @if (!empty($data['bio']))
          <div class="moderator__bio" data-anim="rise">
            {!! $data['bio'] !!}
          </div>
        @endif

        @if (!empty($data['quote']))
          <blockquote class="moderator__quote" data-anim="rise">
            <p>{{ $data['quote'] }}</p>
          </blockquote>
        @endif
      </div>
    </div>
  </div>
</section>
