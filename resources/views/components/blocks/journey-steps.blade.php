<section class="section journey-section">
    <div class="container">
        <div class="section__header fade-in">
            @if(!empty($block['eyebrow']))
                <p class="section__eyebrow">{{ $block['eyebrow'] }}</p>
            @endif

            @if(!empty($block['title']))
                <h2>{{ $block['title'] }}</h2>
            @endif
        </div>

        @if(!empty($block['steps']) && is_array($block['steps']))
            <div class="journey stagger-children">
                @foreach($block['steps'] as $step)
                    <div class="journey__step">
                        @if(!empty($step['number']))
                            <span class="journey__number">{{ $step['number'] }}</span>
                        @endif

                        <div class="journey__content">
                            @if(!empty($step['title']))
                                <h3>{{ $step['title'] }}</h3>
                            @endif

                            @if(!empty($step['description']))
                                <p>{{ $step['description'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
