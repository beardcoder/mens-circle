/**
 * Männerkreis Niederbayern / Straubing — Application Entry Point
 *
 * The frontend uses no UI framework. Each component is a small vanilla TS
 * class (extending `ReactiveHost`) that targets `[data-component="…"]`
 * roots in the server-rendered Blade output.
 *
 * Filament keeps its own Alpine integration for the admin panel — that
 * lives outside this bundle and is unaffected.
 */

import './types';

import { setupSiteHeader } from '@/components/site-header';
import { setupScrollToTop } from '@/components/scroll-to-top';
import { setupCalendar } from '@/components/calendar';
import { setupEventMap } from '@/components/event-map';
import { setupBreathing } from '@/components/breathing';
import {
  setupNewsletterForms,
  setupRegistrationForms,
  setupTestimonialForms,
} from '@/components/forms';
import { initUmamiKit } from '@/utils/umami-kit';

function bootstrap(): void {
  setupSiteHeader();
  setupScrollToTop();
  setupNewsletterForms();
  setupRegistrationForms();
  setupTestimonialForms();
  setupCalendar();
  setupEventMap();
  setupBreathing();
  initUmamiKit();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootstrap, { once: true });
} else {
  bootstrap();
}

if (import.meta.env.DEV && 'PerformanceObserver' in globalThis) {
  new PerformanceObserver((list) => {
    for (const entry of list.getEntries()) {
      if (entry.entryType === 'largest-contentful-paint') {
        // eslint-disable-next-line no-console
        console.debug('LCP:', entry.startTime);
      }
    }
  }).observe({ entryTypes: ['largest-contentful-paint'] });
}
