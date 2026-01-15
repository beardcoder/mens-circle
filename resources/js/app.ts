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
import {
  useIntersectionObserver,
  useSmoothScroll,
  useParallax,
  useLazyImages,
} from '@/composables';

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
  useSmoothScroll();
  useParallax();
  useLazyImages();
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
