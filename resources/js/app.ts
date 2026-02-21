/**
 * Männerkreis Niederbayern / Straubing - Application Entry Point
 * Modern, performant, and accessible web application
 */

import './types';
import { useNavigation, useScrollHeader } from '@/components/navigation';
import { useFAQ } from '@/components/faq';
import {
  useNewsletterForm,
  useRegistrationForm,
  useTestimonialForm,
} from '@/components/forms';
import { useCalendarIntegration } from '@/components/calendar';
import { useIntersectionObserver, useParallax } from '@/composables';
import { useScrollDepthTracking, useExternalLinkTracking } from '@/utils/umami';

/**
 * Initialize all application features when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
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

  // Analytics tracking
  useScrollDepthTracking();
  useExternalLinkTracking();
});

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
