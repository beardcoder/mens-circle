<section class="section moderator-section">
    <div class="container">
        <div class="section__header fade-in">
            @if(!empty($block['eyebrow']))
                <p class="section__eyebrow">{{ $block['eyebrow'] }}</p>
            @endif
        </div>

        <div class="moderator">
            @if(!empty($block['photo']))
                <div class="moderator__image fade-in">
                    <img src="{{ Storage::url($block['photo']) }}" alt="{{ $block['name'] ?? 'Moderator' }}">
                </div>
            @endif

            <div class="moderator__content fade-in fade-in-delay-1">
                @if(!empty($block['name']))
                    <h2>{{ $block['name'] }}</h2>
                @endif

                @if(!empty($block['bio']))
                    <div class="moderator__bio">
                        {!! $block['bio'] !!}
                    </div>
                @endif

                @if(!empty($block['quote']))
                    <blockquote class="moderator__quote">
                        {{ $block['quote'] }}
                    </blockquote>
                @endif
            </div>
        </div>
    </div>
</section>
