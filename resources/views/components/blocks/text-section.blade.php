@php
    $data = $block->data;
@endphp

<section class="section" id="{{ Str::slug($data['title'] ?? '') }}">
    <div class="container container--narrow">
        <div class="section__header fade-in">
            @if(!empty($data['eyebrow']))
                <p class="eyebrow">{{ $data['eyebrow'] }}</p>
            @endif

            @if(!empty($data['title']))
                <h2 class="section-title">{{ $data['title'] }}</h2>
            @endif
        </div>

        @if(!empty($data['content']))
            <div class="section__content fade-in fade-in-delay-1">
                {!! $data['content'] !!}
            </div>
        @endif
    </div>
</section>
