@props (['block' => null])

@php
    $blockData = $block?->data ?? [];
    $anchor = $blockData['anchor'] ?? 'whatsapp-community';
@endphp

<section class="section whatsapp-section" id="{{ $anchor }}">
  <div class="container">
    <div class="whatsapp__layout">
      <div class="whatsapp__content">
        <p class="eyebrow whatsapp__eyebrow">Community</p>
        <h2 class="section-title whatsapp__title">
          Tritt unserer
          <span class="text-italic">WhatsApp Community</span> bei
        </h2>
        <p class="whatsapp__text">Bleibe mit anderen Männern in Verbindung, erhalte Erinnerungen zu unseren Treffen und tausche dich zwischen den Kreisen aus. Ein Raum für Austausch und gegenseitige Unterstützung.</p>
      </div>

      <div class="whatsapp__action">
        <a
          href="{{ $settings->whatsapp_community_link }}"
          target="_blank"
          rel="noopener noreferrer"
          class="btn btn--whatsapp whatsapp__button"
          data-umami-event="whatsapp-click"
          data-umami-event-type="community-join"
        >
          <x-icon name="social-whatsapp" class="whatsapp__icon" />
          <span>Community beitreten</span>
        </a>
        <p class="whatsapp__hint">Kostenlos und unverbindlich</p>
      </div>
    </div>
  </div>
</section>
