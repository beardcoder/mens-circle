/**
 * Event Map
 *
 * Lazy-loaded Leaflet map. Only fetches Leaflet (~150KB) when the element
 * scrolls into view. No-op if lat/lng data attributes are missing.
 */

import { isCoarsePointer } from '@/utils/helpers';
import { mountAll, ReactiveHost } from '@/lib/reactive-host';

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

class EventMap extends ReactiveHost {
  private map: import('leaflet').Map | null = null;
  private initialized = false;

  protected setup(): void {
    const data = readDataset(this.root);

    if (!data) {
      this.root.hidden = true;

      return;
    }

    if (typeof IntersectionObserver === 'undefined') {
      void this.initMap(data);

      return;
    }

    const observer = new IntersectionObserver(
      (entries, obs) => {
        for (const entry of entries) {
          if (entry.isIntersecting) {
            obs.disconnect();
            void this.initMap(data);
            break;
          }
        }
      },
      { rootMargin: '200px' }
    );

    observer.observe(this.root);
    this.signal.addEventListener('abort', () => observer.disconnect(), {
      once: true,
    });
  }

  protected teardown(): void {
    this.map?.remove();
    this.map = null;
  }

  private async initMap(data: MapDataset): Promise<void> {
    if (this.initialized || this.signal.aborted) return;

    this.initialized = true;
    this.root.dataset.state = 'loading';

    const [{ default: L }] = await Promise.all([
      import('leaflet'),
      import('leaflet/dist/leaflet.css'),
    ]);

    if (this.signal.aborted) return;

    const container = this.query<HTMLElement>('.event-map__canvas');

    if (!container) return;

    this.map = L.map(container, {
      scrollWheelZoom: false,
      zoomControl: true,
      attributionControl: true,
    }).setView([data.lat, data.lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(this.map);

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

    L.marker([data.lat, data.lng], { icon }).addTo(this.map).bindPopup(popup);

    this.on(container, 'click', () => this.map?.scrollWheelZoom.enable());
    this.on(container, 'mouseleave', () => this.map?.scrollWheelZoom.disable());

    this.root.dataset.state = 'ready';
  }
}

export function setupEventMap(): void {
  mountAll('[data-component="event-map"]', (el) => new EventMap(el));
}
