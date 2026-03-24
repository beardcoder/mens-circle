/**
 * Männerkreis Niederbayern / Straubing - Application Entry Point
 * Built with @beardcoder/stitch-js progressive enhancement framework
 * Animations powered by anime.js
 */

import './types';
import { register, autoInit } from '@stitch';
import { navigation, scrollHeader, scrollToTop } from '@/components/navigation';
import {
  newsletterForm,
  registrationForm,
  testimonialForm,
} from '@/components/forms';
import { calendarIntegration } from '@/components/calendar';
import {
  scrollAnimate,
  staggerAnimate,
  activeSection,
  journeyProgress,
  heroEntrance,
  breathingCircles,
  scrollPulse,
  animatedGradient,
} from '@/components/scroll-animations';
import { nativeAccordion } from '@/components/accordion';
import { initUmamiKit } from '@/utils/umami-kit';

// Navigation and header
register('#nav', navigation());
register('#header', scrollHeader());
register('#scrollToTop', scrollToTop());

// Hero entrance animation (cinematic reveal sequence)
register('.hero', heroEntrance());

// Decorative breathing circle animations (replaces CSS @keyframes)
register('.hero__circles', breathingCircles());
register('.event-register__circles', breathingCircles());
register('.event-about__circles', breathingCircles());
register('.event-cta__circles', breathingCircles());

// Scroll pulse for hero scroll indicator line
register('.hero__scroll-line', scrollPulse());

// Animated gradient glows on dark background sections
register('.hero__bg', animatedGradient());
register('.journey-section', animatedGradient());
register('.event-register-section', animatedGradient());
register('.event-cta-section', animatedGradient());

// Scroll-driven animations (per-element)
const fadeSelector =
  '.fade-in, .fade-in-up, .fade-in-down, .fade-in-left, .fade-in-right, .fade-in-scale';

register(fadeSelector, scrollAnimate());
register('.stagger-children', staggerAnimate());

// Section tracking (on nav element)
register('#nav', activeSection());

// Journey progress (on section container)
register('.journey-section', journeyProgress());

// Interactive forms
register('#newsletterForm', newsletterForm());
register('#registrationForm', registrationForm());
register('#testimonialForm', testimonialForm());

// Calendar integration
register('#addToCalendar', calendarIntegration());

// Accordion (FAQ sections)
register('.faq-section', nativeAccordion());

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
