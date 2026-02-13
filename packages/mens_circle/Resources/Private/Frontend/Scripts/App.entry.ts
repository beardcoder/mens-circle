/**
 * Männerkreis Niederbayern / Straubing - Application Entry Point
 * Modern, performant, and accessible web application with Hotwired Turbo
 */

import './types';
import * as Turbo from '@hotwired/turbo';
import { useNavigation, useScrollHeader } from './components/navigation';
import { useFAQ } from './components/faq';
import {
  useNewsletterForm,
  useRegistrationForm,
  useTestimonialForm,
} from './components/forms';
import { useCalendarIntegration } from './components/calendar';
import { useIntersectionObserver, useParallax } from './composables';

// Enable Turbo Drive for SPA-like navigation
Turbo.start();

/**
 * Initialize all application features when DOM is ready
 * With Turbo, this needs to be bound to turbo:load instead of DOMContentLoaded
 */
function initializeApp(): void {
  // Navigation and header
  useNavigation();
  useScrollHeader();

  // Interactive components
  useFAQ();
  useNewsletterForm();
  useRegistrationForm();
  useTestimonialForm();
  useCalendarIntegration();

  // Enhanced UX composables
  useIntersectionObserver({ threshold: 0.1, amount: 0.3 });
  useParallax();
}

// Initial load
document.addEventListener('DOMContentLoaded', initializeApp);

// Turbo navigation (when navigating between pages)
document.addEventListener('turbo:load', initializeApp);

// Turbo form submissions (re-initialize after form submit)
document.addEventListener('turbo:frame-load', initializeApp);

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

