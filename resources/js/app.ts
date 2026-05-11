/**
 * Männerkreis Niederbayern / Straubing — Application Entry Point
 */

import './types';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import {
  siteHeader,
  scrollProgress,
  scrollToTop,
} from '@/components/navigation';
import {
  newsletterForm,
  registrationForm,
  testimonialForm,
} from '@/components/forms';
import { calendarIntegration } from '@/components/calendar';
import { eventMap } from '@/components/event-map';
import { breathingApp } from '@/components/breathing';
import { initScrollAnimations } from '@/components/scroll-animations';
import { initUmamiKit } from '@/utils/umami-kit';
import { initSwup } from '@/components/swup-init';

// Install plugins
Alpine.plugin(collapse);

// Register all data components
Alpine.data('siteHeader', siteHeader);
Alpine.data('scrollProgress', scrollProgress);
Alpine.data('scrollToTop', scrollToTop);
Alpine.data('newsletterForm', newsletterForm);
Alpine.data('registrationForm', registrationForm);
Alpine.data('testimonialForm', testimonialForm);
Alpine.data('calendarIntegration', calendarIntegration);
Alpine.data('eventMap', eventMap);
Alpine.data('breathingApp', breathingApp);

// Start Alpine
Alpine.start();

// AJAX page transitions (Swup 4) — replaces #main on link clicks.
const swup = initSwup();

swup.hooks.on('page:view', () => {
  initScrollAnimations();
});

// Analytics tracking
if (document.readyState === 'loading') {
  document.addEventListener(
    'DOMContentLoaded',
    () => {
      initUmamiKit();
      initScrollAnimations();
    },
    {
      once: true,
    }
  );
} else {
  initUmamiKit();
  initScrollAnimations();
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
