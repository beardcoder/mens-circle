/**
 * Männerkreis Niederbayern / Straubing — Application Entry Point
 *
 * Registers Alpine data factories for declarative components and wires up
 * the analytics kit. Heavy components (forms, breathing app, map) live in
 * their own files; this entry stays thin.
 */

import './types';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

import { scrollToTop, siteHeader } from '@/components/navigation';
import {
  newsletterForm,
  registrationForm,
  testimonialForm,
} from '@/components/forms';
import { calendarIntegration } from '@/components/calendar';
import { eventMap } from '@/components/event-map';
import { breathingApp } from '@/components/breathing';
import { initUmamiKit } from '@/utils/umami-kit';

Alpine.plugin(collapse);

Alpine.data('siteHeader', siteHeader);
Alpine.data('scrollToTop', scrollToTop);
Alpine.data('newsletterForm', newsletterForm);
Alpine.data('registrationForm', registrationForm);
Alpine.data('testimonialForm', testimonialForm);
Alpine.data('calendarIntegration', calendarIntegration);
Alpine.data('eventMap', eventMap);
Alpine.data('breathingApp', breathingApp);

Alpine.start();

// Analytics: defer until the document is ready so the tracker never blocks
// first paint or competes with critical JS during page-load.
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => initUmamiKit(), {
    once: true,
  });
} else {
  initUmamiKit();
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
