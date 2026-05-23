/**
 * Event Map Alpine Component
 *
 * Lazy-loaded Leaflet map. Only fetches Leaflet (~150KB) when the element
 * scrolls into view. No-op if lat/lng data attributes are missing.
 */

import { isCoarsePointer } from '@/utils/helpers';
import type { AlpineMagics } from '@/types/alpine';

interface MapDataset {
  lat: number;
  lng: number;
  title: string;
  address: string;
}

function readDataset(el: HTMLElement): MapDataset | null {
  const lat = Number.parseFloat(el.dataset.lat ?? '');
  const lng = Number.parseFloat(el.dataset.lng ?? '');

  if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;

  return {
    lat,
    lng,
    title: el.dataset.title ?? '',
    address: el.dataset.address ?? '',
  };
}

function buildDirectionsUrl(data: MapDataset): string {
  if (isCoarsePointer()) {
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

export function eventMap() {
  const controller = new AbortController();
  let map: import('leaflet').Map | null = null;

  return {
    init(this: AlpineMagics) {
      const el = this.$el;
      const data = readDataset(el);

      if (!data) {
        el.hidden = true;

        return;
      }

      let initialized = false;

      const initMap = async (): Promise<void> => {
        if (initialized || controller.signal.aborted) return;

        initialized = true;
        el.dataset.state = 'loading';

        const [{ default: L }] = await Promise.all([
          import('leaflet'),
          import('leaflet/dist/leaflet.css'),
        ]);

        if (controller.signal.aborted) return;

        const container = el.querySelector<HTMLElement>('.event-map__canvas');

        if (!container) return;

        map = L.map(container, {
          scrollWheelZoom: false,
          zoomControl: true,
          attributionControl: true,
        }).setView([data.lat, data.lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(map);

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

        const { signal } = controller;

        container.addEventListener(
          'click',
          () => map?.scrollWheelZoom.enable(),
          { signal }
        );
        container.addEventListener(
          'mouseleave',
          () => map?.scrollWheelZoom.disable(),
          { signal }
        );

        el.dataset.state = 'ready';
      };

      if (typeof IntersectionObserver === 'undefined') {
        void initMap();

        return;
      }

      const observer = new IntersectionObserver(
        (entries, obs) => {
          for (const entry of entries) {
            if (entry.isIntersecting) {
              obs.disconnect();
              void initMap();
              break;
            }
          }
        },
        { rootMargin: '200px' }
      );

      observer.observe(el);
      controller.signal.addEventListener('abort', () => observer.disconnect(), {
        once: true,
      });
    },

    destroy() {
      controller.abort();
      map?.remove();
      map = null;
    },
  };
}
