@php
    $data = $block->data;
@endphp

<section class="intro-section" id="ueber" aria-labelledby="intro-title">
    <div class="intro__layout">
        <div class="intro__left">
            @if(!empty($data['eyebrow']))
                <p class="eyebrow fade-in">{{ $data['eyebrow'] }}</p>
            @endif

            @if(!empty($data['title']))
                <h2 class="section-title intro__title fade-in fade-in-delay-1" id="intro-title">
                    {!! $data['title'] !!}
                </h2>
            @endif

            @if(!empty($data['text']))
                <p class="intro__text fade-in fade-in-delay-2">
                    {{ $data['text'] }}
                </p>
            @endif

            @if(!empty($data['values']) && is_array($data['values']))
                <div class="intro__values stagger-children">
                    @foreach($data['values'] as $value)
                        <div class="value-item">
                            @if(!empty($value['number']))
                                <span class="value-item__number">{{ $value['number'] }}</span>
                            @endif
                            <div class="value-item__content">
                                @if(!empty($value['title']))
                                    <h4>{{ $value['title'] }}</h4>
                                @endif
                                @if(!empty($value['description']))
                                    <p>{{ $value['description'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="intro__right">
            <div class="intro__image-area">
                <div class="intro__image-circles"></div>
                @if(!empty($data['quote']))
                    <p class="intro__image-text">
                        {!! $data['quote'] !!}
                    </p>
                @endif
            </div>
        </div>
    </div>
</section>
