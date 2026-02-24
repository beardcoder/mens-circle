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
                        @if($icon === 'warrior')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="M12 3.5 16 5v3.8c0 3.6-1.8 6.4-4 8-2.2-1.6-4-4.4-4-8V5l4-1.5z" />
                                <path d="M8.2 12.8 4.8 16.2" />
                                <path d="M15.8 12.8 19.2 16.2" />
                                <path d="M7 18h10" />
                                <path d="M12 7.7v5.7" />
                            </svg>
                        @elseif($icon === 'lover')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="M12 19.4c-2.8-2.3-7.1-5.5-7.1-8.8C4.9 8.5 6.3 7 8.3 7c1.5 0 2.8.8 3.7 2.1.9-1.3 2.2-2.1 3.7-2.1 2 0 3.4 1.5 3.4 3.6 0 3.3-4.3 6.5-7.1 8.8z" />
                                <path d="M8.7 15.2c1 .7 2.1 1.2 3.3 1.4" />
                                <path d="M15.3 15.2c-1 .7-2.1 1.2-3.3 1.4" />
                            </svg>
                        @elseif($icon === 'magician')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="m12 3.8 1.5 3.7 3.8 1.5-2.9 2.4.9 3.8-3.3-2.1-3.3 2.1.9-3.8-2.9-2.4 3.8-1.5L12 3.8z" />
                                <path d="M12 18.5a6.3 6.3 0 0 0 6.3-6.3" />
                                <path d="M5.7 12.2A6.3 6.3 0 0 1 12 5.9" />
                                <path d="M19 5.2h.01" />
                                <path d="M6 18.8h.01" />
                            </svg>
                        @elseif($icon === 'king')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="m4.5 17.7 1.2-8.2 4.3 3.6L12 7.8l2 5.3 4.3-3.6 1.2 8.2z" />
                                <path d="M7 17.7h10" />
                                <path d="M8.4 20h7.2" />
                                <path d="M8.4 9.5h.01" />
                                <path d="M12 7.8h.01" />
                                <path d="M15.6 9.5h.01" />
                            </svg>
                        @elseif($icon === 'father')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="M12 5.2c3.6 0 6.5 2.9 6.5 6.5S15.6 18.2 12 18.2 5.5 15.3 5.5 11.7 8.4 5.2 12 5.2z" />
                                <path d="M12 8.1v7.2" />
                                <path d="M9.6 10.5c.8-.8 1.6-1.2 2.4-1.2.9 0 1.7.4 2.4 1.2" />
                                <path d="M9 20.2c1-.9 2-1.3 3-1.3s2 .4 3 1.3" />
                            </svg>
                        @else
                            <svg viewBox="0 0 24 24" role="presentation">
                                <circle cx="12" cy="12" r="7.2" />
                                <path d="M8.7 12a3.3 3.3 0 0 1 6.6 0c0 1.8-1.5 3.3-3.3 3.3S8.7 13.8 8.7 12z" />
                            </svg>
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
