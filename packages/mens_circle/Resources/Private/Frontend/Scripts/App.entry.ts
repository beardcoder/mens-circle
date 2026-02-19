/**
 * Männerkreis Niederbayern / Straubing - Application Entry Point
 * Modern, performant, and accessible web application powered by Hotwired Turbo
 */

import '@hotwired/turbo';
import './utils/sentry';
import './types';
import { useNavigation, useScrollHeader } from './components/navigation';
import { useFAQ } from './components/faq';
import { useCalendarIntegration } from './components/calendar';
import { useIntersectionObserver, useParallax } from './composables';

/**
 * Initialize all application features.
 * Called on initial load and after every Turbo navigation via turbo:load.
 */
function initComponents(): void {
  // Navigation and header (both have internal cleanup for re-initialization)
  useNavigation();
  useScrollHeader();

  // Interactive components
  useFAQ();
  useCalendarIntegration();

  // Enhanced UX composables
  useIntersectionObserver({ threshold: 0.1, amount: 0.3 });
  useParallax();
}

// Run on initial page load and every Turbo navigation
document.addEventListener('turbo:load', initComponents);

// Performance monitoring (only in development)
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
