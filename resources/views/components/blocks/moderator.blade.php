@props([
    'block',
    'page' => null,
])

@php
    $blockId = $block['data']['block_id'] ?? null;
    $media = $page?->getBlockMedia($blockId, 'photo');

    if ($media) {
        $media->name = strip_tags($block['data']['name'] ?? 'Moderator');
    }
@endphp

<section class="section moderator-section" id="moderator">
    <div class="container">
        <div class="moderator__layout">
            <div class="moderator__photo-wrapper fade-in">
                <div class="moderator__photo">
                    @if($media)
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

            <div class="moderator__content fade-in fade-in-delay-1">
                @if(!empty($block['data']['eyebrow']))
                    <p class="moderator__eyebrow">{{ $block['data']['eyebrow'] }}</p>
                @endif

                @if(!empty($block['data']['name']))
                    <h2 class="moderator__name">{!! $block['data']['name'] !!}</h2>
                @endif

                @if(!empty($block['data']['bio']))
                    <div class="moderator__bio">
                        {!! $block['data']['bio'] !!}
                    </div>
                @endif

                @if(!empty($block['data']['quote']))
                    <blockquote class="moderator__quote">
                        <p>{{ $block['data']['quote'] }}</p>
                    </blockquote>
                @endif
            </div>
        </div>
    </div>
</section>
