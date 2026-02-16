/**
 * Männerkreis Niederbayern / Straubing - Application Entry Point
 * Modern, performant, and accessible web application
 */

import './utils/sentry';
import './types';
import { useNavigation, useScrollHeader } from './components/navigation';
import { useFAQ } from './components/faq';
import { useCalendarIntegration } from './components/calendar';
import { useFormEnhancer } from './components/form-enhancer';
import { useIntersectionObserver, useParallax } from './composables';

/**
 * Initialize all application features.
 */
function initComponents(): void {
  // Navigation and header
  useNavigation();
  useScrollHeader();

  // Interactive components
  useFAQ();
  useCalendarIntegration();
  useFormEnhancer();

  // Enhanced UX composables
  useIntersectionObserver({ threshold: 0.1, amount: 0.3 });
  useParallax();
}

// Run once on load
initComponents();

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
