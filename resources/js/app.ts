/**
 * Männerkreis Niederbayern / Straubing - Application Entry Point
 * Modern, performant, and accessible web application
 */

import './types';
import { initNavigation, initScrollHeader } from '@/components/navigation';
import { initFAQ } from '@/components/faq';
import { initForms } from '@/components/forms';
import { initCalendarIntegration } from '@/components/calendar';
import {
  useIntersectionObserver,
  useSmoothScroll,
  useParallax,
  useViewTransitions,
  useLazyImages,
  usePrefetch,
} from '@/composables';

/**
 * Initialize all application features when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
  // Legacy components (to be gradually refactored)
  initNavigation();
  initScrollHeader();
  initFAQ();
  initForms();
  initCalendarIntegration();

  // Modern composables for better performance and UX
  useIntersectionObserver({ threshold: 0.1, amount: 0.3 });
  useSmoothScroll();
  useParallax();
  useViewTransitions();
  useLazyImages();
  usePrefetch();
});

// Performance monitoring
if ('PerformanceObserver' in window) {
  const perfObserver = new PerformanceObserver((list) => {
    list.getEntries().forEach((entry) => {
      if (entry.entryType === 'largest-contentful-paint') {
        console.debug('LCP:', entry.startTime);
      }
    });
  });

  perfObserver.observe({ entryTypes: ['largest-contentful-paint'] });
}

// Service Worker registration for PWA capabilities (optional future enhancement)
if ('serviceWorker' in navigator && import.meta.env.PROD) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {
      // Silent fail - SW is optional
    });
  });
}

