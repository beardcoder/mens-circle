<section class="section section--large journey-section" id="reise">
    <div class="container">
        <div class="journey__header fade-in">
            @if(!empty($block['data']['eyebrow']))
                <p class="eyebrow">{{ $block['data']['eyebrow'] }}</p>
            @endif

            @if(!empty($block['data']['title']))
                <h2 class="section-title journey__title">{!! $block['data']['title'] !!}</h2>
            @endif

            @if(!empty($block['data']['subtitle']))
                <p class="journey__subtitle">{{ $block['data']['subtitle'] }}</p>
            @endif
        </div>

        @if(!empty($block['data']['steps']) && is_array($block['data']['steps']))
            <div class="journey__steps stagger-children">
                @foreach($block['data']['steps'] as $step)
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
