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
                                <path d="M12 3.6 15.8 5v4.2c0 3.4-1.7 6.1-3.8 7.7-2.1-1.6-3.8-4.3-3.8-7.7V5L12 3.6z" />
                                <path d="M12 8v6.5" />
                                <path d="M10 10.8h4" />
                                <path d="M8.4 18.3 12 17l3.6 1.3" />
                            </svg>
                        @elseif($icon === 'lover')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="M12 19.2c-2.9-2.2-7-5.2-7-8.7 0-2.1 1.5-3.6 3.5-3.6 1.4 0 2.6.7 3.5 2 .9-1.3 2.1-2 3.5-2 2 0 3.5 1.5 3.5 3.6 0 3.5-4.1 6.5-7 8.7z" />
                                <path d="M8.5 12.7c1 .7 2.2 1.1 3.5 1.2" />
                                <path d="M15.5 12.7c-1 .7-2.2 1.1-3.5 1.2" />
                            </svg>
                        @elseif($icon === 'magician')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="m12 4.2 1.4 3.2 3.5 1.3-2.7 2.2.8 3.5-3-1.9-3 1.9.8-3.5-2.7-2.2 3.5-1.3L12 4.2z" />
                                <path d="M17.9 16.7a8.2 8.2 0 0 1-11.8 0" />
                                <path d="M6.1 7.3a8.2 8.2 0 0 1 11.8 0" />
                            </svg>
                        @elseif($icon === 'king')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="m4.7 17.8 1.1-8 4.2 3.5L12 8l2 5.3 4.2-3.5 1.1 8z" />
                                <path d="M7.2 17.8h9.6" />
                                <path d="M8.5 20h7" />
                                <path d="M10 10.2 12 8l2 2.2" />
                            </svg>
                        @elseif($icon === 'father')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="M12 4.8a6.8 6.8 0 1 1 0 13.6 6.8 6.8 0 0 1 0-13.6z" />
                                <path d="M12 8v7.4" />
                                <path d="M10 10.1c.7-.6 1.4-.9 2-.9s1.3.3 2 .9" />
                                <path d="M9 20.4c1-.8 2-1.2 3-1.2s2 .4 3 1.2" />
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
