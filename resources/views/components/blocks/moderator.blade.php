@props ([
    'block',
    'page' => null,
])

@php
    $data = $block->data;
    $anchor = $data['anchor'] ?? 'moderator';
    $media = $block->getFieldMedia('photo');

    if ($media) {
        $media->name = strip_tags($data['name'] ?? 'Moderator');
    }
@endphp

<section class="section moderator-section" id="{{ $anchor }}">
  <span class="moderator__ornament" aria-hidden="true">BEGLEITER</span>
  <div class="container">
    <div class="moderator__layout">
      <div class="moderator__photo-wrapper" data-aos="fade-right">
        <div class="moderator__photo" data-motion="image">
          @if ($media)
            {{ $media->img()->attributes([
                            'loading' => 'lazy',
                            'decoding' => 'async',
                        ]) }}
          @else
            <div class="moderator__photo-placeholder">
              <x-sprite name="user" />
              <span>Foto</span>
            </div>
          @endif
        </div>
        <div class="moderator__photo-accent"></div>
      </div>

      <div class="moderator__content" data-aos="fade-left" data-aos-delay="120">
        @if (!empty($data['eyebrow']))
          <p class="eyebrow">{{ $data['eyebrow'] }}</p>
        @endif

        @if (!empty($data['name']))
          <h2 class="moderator__name">{!! $data['name'] !!}</h2>
        @endif

        @if (!empty($data['bio']))
          <div class="moderator__bio">{!! $data['bio'] !!}</div>
        @endif

        @if (!empty($data['quote']))
          <blockquote class="moderator__quote">
            <p>{{ $data['quote'] }}</p>
          </blockquote>
        @endif
      </div>
    </div>
  </div>
</section>
