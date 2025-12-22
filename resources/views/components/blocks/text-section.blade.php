<section class="section" id="{{ Str::slug($block->data['title'] ?? '') }}">
    <div class="container container--narrow">
        <div class="section__header fade-in">
            @if(!empty($block->data['eyebrow']))
                <p class="section__eyebrow">{{ $block->data['eyebrow'] }}</p>
            @endif

            @if(!empty($block->data['title']))
                <h2>{{ $block->data['title'] }}</h2>
            @endif
        </div>

        @if(!empty($block->data['content']))
            <div class="section__content fade-in fade-in-delay-1">
                {!! $block->data['content'] !!}
            </div>
        @endif
    </div>
</section>
