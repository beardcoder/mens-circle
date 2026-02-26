/**
 * Männerkreis Niederbayern / Straubing - Application Entry Point
 * Modern, performant, and accessible web application
 */

import './types';
import {
  useNavigation,
  useScrollHeader,
  useScrollToTop,
} from '@/components/navigation';
import {
  useNewsletterForm,
  useRegistrationForm,
  useTestimonialForm,
} from '@/components/forms';
import { useCalendarIntegration } from '@/components/calendar';
import {
  useScrollAnimations,
  useActiveSection,
  useJourneyProgress,
} from '@/components/scroll-animations';
import { initUmamiKit } from '@/utils/umami-kit';

/**
 * Initialize all application features when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
  // Navigation and header
  useNavigation();
  useScrollHeader();
  useScrollToTop();

  // Scroll-driven animations and section tracking
  useScrollAnimations();
  useActiveSection();
  useJourneyProgress();

  // Interactive components
  useNewsletterForm();
  useRegistrationForm();
  useTestimonialForm();
  useCalendarIntegration();

  // Analytics tracking
  initUmamiKit();
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
