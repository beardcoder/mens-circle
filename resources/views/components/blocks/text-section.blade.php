<section class="section" id="{{ Str::slug($block['title'] ?? '') }}">
    <div class="container container--narrow">
        <div class="section__header fade-in">
            @if(!empty($block['eyebrow']))
                <p class="section__eyebrow">{{ $block['eyebrow'] }}</p>
            @endif

            @if(!empty($block['title']))
                <h2>{{ $block['title'] }}</h2>
            @endif
        </div>

        @if(!empty($block['content']))
            <div class="section__content fade-in fade-in-delay-1">
                {!! $block['content'] !!}
            </div>
        @endif
    </div>
</section>
