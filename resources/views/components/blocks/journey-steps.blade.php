@php
    $data = $block->data;
@endphp

<section class="section section--large journey-section" id="reise">
    <div class="container">
        <div class="journey__header fade-in">
            @if(!empty($data['eyebrow']))
                <p class="eyebrow">{{ $data['eyebrow'] }}</p>
            @endif

            @if(!empty($data['title']))
                <h2 class="section-title journey__title">{!! $data['title'] !!}</h2>
            @endif

            @if(!empty($data['subtitle']))
                <p class="journey__subtitle">{{ $data['subtitle'] }}</p>
            @endif
        </div>

        @if(!empty($data['steps']) && is_array($data['steps']))
            <div class="journey__steps stagger-children">
                @foreach($data['steps'] as $step)
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
