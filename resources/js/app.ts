/**
 * Männerkreis Niederbayern / Straubing - Application Entry Point
 * Built with @beardcoder/stitch-js progressive enhancement framework
 */

import './types';
import { register, autoInit } from '@beardcoder/stitch-js';
import {
  navigation,
  scrollHeader,
  scrollProgress,
  scrollToTop,
} from '@/components/navigation';
import {
  newsletterForm,
  registrationForm,
  testimonialForm,
} from '@/components/forms';
import { calendarIntegration } from '@/components/calendar';
import { nativeAccordion } from '@/components/accordion';
import { breathingApp } from '@/components/breathing';
import { scrollAnimations } from '@/components/scroll-animations';
import { initUmamiKit } from '@/utils/umami-kit';
import { initSwup } from '@/components/swup-init';

// Navigation and header
register('#nav', navigation());
register('#header', scrollHeader());
register('#scrollToTop', scrollToTop());
register('.scroll-progress', scrollProgress());

// Interactive forms
register('#newsletterForm', newsletterForm());
register('#registrationForm', registrationForm());
register('#testimonialForm', testimonialForm());

// Calendar integration
register('#addToCalendar', calendarIntegration());

// Accordion (FAQ sections)
register('.faq-section', nativeAccordion());

// Breathing exercise
register('#breathingApp', breathingApp());

// Scroll-triggered reveal animations (IntersectionObserver fallback —
// native animation-timeline: view() handles supporting browsers via CSS)
register('body', scrollAnimations());

// Initialize all registered components
autoInit();

// AJAX page transitions (Swup 4) — owns native View Transitions for
// supporting browsers and replaces #main on link clicks.
initSwup();

// Analytics tracking (standalone, not a stitch component)
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => initUmamiKit(), {
    once: true,
  });
} else {
  initUmamiKit();
}

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
