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
                <article class="archetype-card">
                    <div class="archetype-card__icon" aria-hidden="true">
                        @if($icon === 'warrior')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="M12 3l7 3v5c0 5-3 8-7 10-4-2-7-5-7-10V6l7-3z" />
                                <path d="M12 8v8" />
                                <path d="M9 11h6" />
                            </svg>
                        @elseif($icon === 'lover')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="M12 20s-6.5-4.4-8.6-8C1.8 8.9 3.2 6 6.3 6c2 0 3.1 1.1 3.7 2 0.6-.9 1.7-2 3.7-2 3.1 0 4.5 2.9 2.9 6-2.1 3.6-8.6 8-8.6 8z" />
                            </svg>
                        @elseif($icon === 'magician')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="M12 3l1.8 4.6L19 9.4l-4 3.4 1.2 5.2L12 15.3 7.8 18l1.2-5.2-4-3.4 5.2-1.8L12 3z" />
                                <path d="M20.5 4.5l-1 1" />
                                <path d="M18.5 2.5l-1 1" />
                            </svg>
                        @elseif($icon === 'king')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="M4 18h16l-1.5-9-4.5 4-2-5-2 5-4.5-4L4 18z" />
                                <path d="M6 20h12" />
                            </svg>
                        @elseif($icon === 'father')
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="M12 5a7 7 0 1 0 7 7" />
                                <path d="M12 9a3 3 0 1 0 3 3" />
                                <path d="M19 5v4h-4" />
                            </svg>
                        @else
                            <svg viewBox="0 0 24 24" role="presentation">
                                <circle cx="12" cy="12" r="8" />
                                <path d="M12 8v8" />
                            </svg>
                        @endif
                    </div>

                    <span class="archetype-card__number">
                        {{ $item['number'] ?? $loop->iteration }}
                    </span>

                    @if(!empty($item['title']))
                        <h3 class="archetype-card__title">{{ $item['title'] }}</h3>
                    @endif

                    @if(!empty($item['description']))
                        <p class="archetype-card__description">{{ $item['description'] }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
