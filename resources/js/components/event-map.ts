/**
 * Event Map — lazy-loaded Leaflet map
 *
 * Only fetches Leaflet (~150 KB) once the element scrolls into view.
 * Factory style; aborts the in-flight load if the component is
 * destroyed before Leaflet finishes loading.
 */

import { isCoarsePointer } from '@/utils/helpers';
import { defineComponent } from '@beardcoder/lume';

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

export default defineComponent(({ root, on, cleanup }) => {
  const data = readDataset(root);

  let map: import('leaflet').Map | null = null;
  let initialized = false;
  let disposed = false;

  if (!data) {
    root.hidden = true;

    return {};
  }

  const initMap = async (): Promise<void> => {
    if (initialized || disposed) return;

    initialized = true;
    root.dataset.state = 'loading';

    const [{ default: L }] = await Promise.all([
      import('leaflet'),
      import('leaflet/dist/leaflet.css'),
    ]);

    if (disposed) return;

    const container = root.querySelector<HTMLElement>('.event-map__canvas');

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

    on(container, 'click', () => map?.scrollWheelZoom.enable());
    on(container, 'mouseleave', () => map?.scrollWheelZoom.disable());

    root.dataset.state = 'ready';
  };

  if (typeof IntersectionObserver === 'undefined') {
    void initMap();
  } else {
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

    observer.observe(root);
    cleanup(() => observer.disconnect());
  }

  cleanup(() => {
    disposed = true;
    map?.remove();
    map = null;
  });

  return {};
});
