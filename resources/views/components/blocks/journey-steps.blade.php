<section class="section section--large journey-section" id="reise">
    <div class="container">
        <div class="journey__header fade-in">
            @if(!empty($block['eyebrow']))
                <p class="journey__eyebrow">{{ $block['eyebrow'] }}</p>
            @endif

            @if(!empty($block['title']))
                <h2 class="journey__title">{!! $block['title'] !!}</h2>
            @endif

            @if(!empty($block['subtitle']))
                <p class="journey__subtitle">{{ $block['subtitle'] }}</p>
            @endif
        </div>

        @if(!empty($block['steps']) && is_array($block['steps']))
            <div class="journey__steps stagger-children">
                @foreach($block['steps'] as $step)
                    <div class="journey__step">
                        @if(!empty($step['number']))
                            <div class="journey__step-number">{{ $step['number'] }}</div>
                        @endif

                        @if(!empty($step['title']))
                            <h3 class="journey__step-title">{{ $step['title'] }}</h3>
                        @endif

                        @if(!empty($step['description']))
                            <p class="journey__step-text">{{ $step['description'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
