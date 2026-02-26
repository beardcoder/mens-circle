@props(['block'])

@php
    $data = $block->data;
    $items = $data['items'] ?? [];
    $detectIcon = static function (array $item): string {
        $title = mb_strtolower((string) ($item['title'] ?? ''));

        return match (true) {
            str_contains($title, 'krieger') => 'warrior',
            str_contains($title, 'liebhaber') => 'lover',
            str_contains($title, 'zauberer') => 'magician',
            str_contains($title, 'könig'), str_contains($title, 'koenig') => 'king',
            str_contains($title, 'vater') => 'father',
            default => 'neutral',
        };
    };
@endphp

@if(!empty($items) && is_array($items))
<section class="section section--large archetypes-section" id="archetypen" aria-labelledby="archetypes-title">
    <div class="container">
        <div class="archetypes__header fade-in">
            @if(!empty($data['eyebrow']))
                <p class="eyebrow">{{ $data['eyebrow'] }}</p>
            @endif

            @if(!empty($data['title']))
                <h2 class="section-title archetypes__title" id="archetypes-title">{{ $data['title'] }}</h2>
            @endif

            @if(!empty($data['intro']))
                <p class="archetypes__intro">{{ $data['intro'] }}</p>
            @endif
        </div>

        <div class="archetypes__grid stagger-children">
            @foreach($items as $item)
                @php $icon = $detectIcon($item); @endphp
                <article class="archetype-card archetype-card--{{ $icon }}">
                    <div class="archetype-card__background-icon" aria-hidden="true">
                        @php $svgPath = public_path('images/archetypes/' . (in_array($icon, ['warrior', 'lover', 'magician', 'king', 'father'], true) ? $icon : 'neutral') . '.svg'); @endphp
                        @if(file_exists($svgPath))
                            {!! file_get_contents($svgPath) !!}
                        @endif
                    </div>

                    <div class="archetype-card__content">
                        @if(!empty($item['title']))
                            <h3 class="archetype-card__title">{{ $item['title'] }}</h3>
                        @endif

                        @if(!empty($item['description']))
                            <p class="archetype-card__description">{{ $item['description'] }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
