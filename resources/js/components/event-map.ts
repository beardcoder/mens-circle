/**
 * Event Map Component
 *
 * Lazy-loaded Leaflet map for the event detail page. Leaflet (~150KB) is only
 * fetched when the user scrolls the map container into view, keeping the
 * initial bundle small. The component is a no-op if coordinates are missing.
 *
 * Renders OpenStreetMap tiles, a single marker for the event location, and a
 * popup with the address + a "Route planen" link that opens the user's default
 * maps app (uses geo: URI on mobile, OSM directions on desktop).
 */

import { defineComponent } from '@beardcoder/stitch-js';

interface EventMapOptions {
  zoom: number;
  rootMargin: string;
}

interface MapDataset {
  lat: number;
  lng: number;
  title: string;
  address: string;
}

function readDataset(el: HTMLElement): MapDataset | null {
  const lat = Number.parseFloat(el.dataset.lat ?? '');
  const lng = Number.parseFloat(el.dataset.lng ?? '');

  if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
    return null;
  }

  return {
    lat,
    lng,
    title: el.dataset.title ?? '',
    address: el.dataset.address ?? '',
  };
}

function buildDirectionsUrl(data: MapDataset): string {
  const isCoarsePointer =
    typeof globalThis.matchMedia === 'function' &&
    globalThis.matchMedia('(pointer: coarse)').matches;

  if (isCoarsePointer) {
    const label = encodeURIComponent(data.address || data.title);

    return `geo:${data.lat},${data.lng}?q=${data.lat},${data.lng}(${label})`;
  }

  return `https://www.openstreetmap.org/directions?to=${data.lat}%2C${data.lng}`;
}

function escapeHtml(value: string): string {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

export const eventMap = defineComponent<EventMapOptions>(
  {
    zoom: 16,
    rootMargin: '200px',
  },
  (ctx) => {
    const el = ctx.el as HTMLElement;
    const data = readDataset(el);

    if (!data) {
      el.hidden = true;

      return;
    }

    let initialized = false;

    const init = async (): Promise<void> => {
      if (initialized) return;
      initialized = true;

      el.dataset.state = 'loading';

      const [{ default: L }] = await Promise.all([
        import('leaflet'),
        import('leaflet/dist/leaflet.css'),
      ]);

      const container = el.querySelector<HTMLElement>('.event-map__canvas');

      if (!container) return;

      const map = L.map(container, {
        scrollWheelZoom: false,
        zoomControl: true,
        attributionControl: true,
      }).setView([data.lat, data.lng], ctx.options.zoom);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution:
          '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      }).addTo(map);

      // Custom marker — uses inline SVG so we don't ship Leaflet's PNG assets.
      const icon = L.divIcon({
        className: 'event-map__marker',
        html:
          '<svg viewBox="0 0 32 44" aria-hidden="true" focusable="false">' +
          '<path d="M16 0C7.2 0 0 7 0 15.5 0 27 16 44 16 44s16-17 16-28.5C32 7 24.8 0 16 0z"/>' +
          '<circle cx="16" cy="15.5" r="6" fill="#fff"/>' +
          '</svg>',
        iconSize: [32, 44],
        iconAnchor: [16, 44],
        popupAnchor: [0, -40],
      });

      const popup =
        '<strong>' +
        escapeHtml(data.title) +
        '</strong>' +
        (data.address ? '<br>' + escapeHtml(data.address) : '') +
        '<br><a class="event-map__directions" href="' +
        buildDirectionsUrl(data) +
        '" target="_blank" rel="noopener">Route planen</a>';

      L.marker([data.lat, data.lng], { icon }).addTo(map).bindPopup(popup);

      // Re-enable wheel zoom only after a click — prevents accidental scroll hijack.
      const enableWheel = (): void => {
        map.scrollWheelZoom.enable();
      };
      const disableWheel = (): void => {
        map.scrollWheelZoom.disable();
      };

      container.addEventListener('click', enableWheel);
      container.addEventListener('mouseleave', disableWheel);

      ctx.onDestroy(() => {
        container.removeEventListener('click', enableWheel);
        container.removeEventListener('mouseleave', disableWheel);
        map.remove();
      });

      el.dataset.state = 'ready';
    };

    if (typeof IntersectionObserver === 'undefined') {
      void init();

      return;
    }

    const observer = new IntersectionObserver(
      (entries, obs) => {
        for (const entry of entries) {
          if (entry.isIntersecting) {
            obs.disconnect();
            void init();
            break;
          }
        }
      },
      { rootMargin: ctx.options.rootMargin }
    );

    observer.observe(el);

    ctx.onDestroy(() => observer.disconnect());
  }
);
