/**
 * Männerkreis Niederbayern / Straubing - Application Entry Point
 * Built with @beardcoder/stitch-js progressive enhancement framework
 */

import './types';
import { register, autoInit } from '@beardcoder/stitch-js';
import { navigation, scrollHeader, scrollToTop } from '@/components/navigation';
import {
  newsletterForm,
  registrationForm,
  testimonialForm,
} from '@/components/forms';
import { calendarIntegration } from '@/components/calendar';
import {
  reveal,
  revealStagger,
  activeSection,
  journeyProgress,
} from '@/components/motion-reveal';
import { nativeAccordion } from '@/components/accordion';
import { breathingApp } from '@/components/breathing';
import { initUmamiKit } from '@/utils/umami-kit';

// Navigation and header
register('#nav', navigation());
register('#header', scrollHeader());
register('#scrollToTop', scrollToTop());

// Scroll reveal — one calm motion for any of these markers
const revealSelector =
  '.fade-in, .fade-in-up, .fade-in-down, .fade-in-left, .fade-in-right, .fade-in-scale';

register(revealSelector, reveal());
register('.stagger-children, [data-reveal-stagger]', revealStagger());

// Orientation — active nav link and journey step feedback
register('#nav', activeSection());
register('.journey-section', journeyProgress());

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

// Initialize all registered components
autoInit();

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
