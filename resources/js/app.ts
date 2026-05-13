/**
 * Männerkreis Niederbayern / Straubing — Application Entry Point
 */

import './types';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { siteHeader, scrollToTop } from '@/components/navigation';
import {
  newsletterForm,
  registrationForm,
  testimonialForm,
} from '@/components/forms';
import { calendarIntegration } from '@/components/calendar';
import { eventMap } from '@/components/event-map';
import { breathingApp } from '@/components/breathing';
import { initUmamiKit } from '@/utils/umami-kit';

// Install plugins
Alpine.plugin(collapse);

// Register all data components
Alpine.data('siteHeader', siteHeader);
Alpine.data('scrollToTop', scrollToTop);
Alpine.data('newsletterForm', newsletterForm);
Alpine.data('registrationForm', registrationForm);
Alpine.data('testimonialForm', testimonialForm);
Alpine.data('calendarIntegration', calendarIntegration);
Alpine.data('eventMap', eventMap);
Alpine.data('breathingApp', breathingApp);

// Start Alpine
Alpine.start();

// Analytics tracking
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => initUmamiKit(), {
    once: true,
  });
} else {
  initUmamiKit();
}

// LCP monitoring in development
if (import.meta.env.DEV && 'PerformanceObserver' in globalThis) {
  const perfObserver = new PerformanceObserver((list) => {
    list.getEntries().forEach((entry) => {
      if (entry.entryType === 'largest-contentful-paint') {
        // eslint-disable-next-line no-console
        console.debug('LCP:', entry.startTime);
      }
    });
  });

  perfObserver.observe({ entryTypes: ['largest-contentful-paint'] });
}
