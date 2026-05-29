/**
 * Männerkreis Niederbayern / Straubing — Application Entry Point
 *
 * The frontend uses Lume for small reactive components on top of
 * server-rendered Blade output.
 *
 * Filament keeps its own Alpine integration for the admin panel — that
 * lives outside this bundle and is unaffected.
 */

import './types';

import { createLume } from '@beardcoder/lume';

import breathing from '@/components/breathing';
import calendar from '@/components/calendar';
import eventMap from '@/components/event-map';
import {
  newsletterForm,
  registrationForm,
  testimonialForm,
} from '@/components/forms';
import siteHeader from '@/components/site-header';
import { initMotion } from '@/utils/motion';
import { initUmamiKit } from '@/utils/umami-kit';

function bootstrap(): void {
  createLume()
    .component('site-header', siteHeader)
    .component('newsletter-form', newsletterForm)
    .component('registration-form', registrationForm)
    .component('testimonial-form', testimonialForm)
    .component('calendar', calendar)
    .component('event-map', eventMap)
    .component('breathing-app', breathing)
    .mount();

  initMotion();
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
