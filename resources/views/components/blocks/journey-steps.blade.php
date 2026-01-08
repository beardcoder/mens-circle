@php
    $data = $block->data;
    $steps = $data['steps'] ?? [];
@endphp

@if(!empty($steps) && is_array($steps))
@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "HowTo",
    "name": "{{ strip_tags($data['title'] ?? 'Deine Reise zum Männerkreis') }}",
    "description": "{{ $data['subtitle'] ?? 'Wie du Teil des Männerkreis wirst' }}",
    "step": [
        @foreach($steps as $step)
        {
            "@@type": "HowToStep",
            "position": {{ $loop->iteration }},
            "name": "{{ e($step['title'] ?? '') }}",
            "text": "{{ e($step['description'] ?? '') }}"
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endpush
@endif

<section class="section section--large journey-section" id="reise" aria-labelledby="journey-title">
    <div class="container">
        <div class="journey__header fade-in">
            @if(!empty($data['eyebrow']))
                <p class="eyebrow">{{ $data['eyebrow'] }}</p>
            @endif

            @if(!empty($data['title']))
                <h2 class="section-title journey__title" id="journey-title">{!! $data['title'] !!}</h2>
            @endif

            @if(!empty($data['subtitle']))
                <p class="journey__subtitle">{{ $data['subtitle'] }}</p>
            @endif
        </div>

        @if(!empty($steps) && is_array($steps))
            <div class="journey__steps stagger-children">
                @foreach($steps as $step)
                    <div class="journey__step">
                        @if(!empty($step['number']))
                            <div class="journey__step-number" aria-hidden="true">{{ $step['number'] }}</div>
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
