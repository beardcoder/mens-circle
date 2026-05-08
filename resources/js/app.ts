/**
 * Männerkreis Niederbayern / Straubing - Application Entry Point
 * Built with Alpine.js for progressive enhancement
 */

import './types';
import './alpine'; // Initialize Alpine.js
import { accordion } from '@/components/accordion-alpine';
import { mobileNav } from '@/components/navigation-alpine';
import { breathingApp } from '@/components/breathing-alpine';
import {
  newsletterForm,
  registrationForm,
  testimonialForm,
  testimonialCharCounter,
} from '@/components/forms';
import { calendarIntegration } from '@/components/calendar';
import { eventMap } from '@/components/event-map';
import {
  scrollHeader,
  scrollToTop,
  scrollProgress,
} from '@/components/navigation';
import { initUmamiKit } from '@/utils/umami-kit';
import { initSwup } from '@/components/swup-init';

// Make Alpine components globally available
declare global {
  interface Window {
    accordion: typeof accordion;
    mobileNav: typeof mobileNav;
    breathingApp: typeof breathingApp;
    testimonialCharCounter: typeof testimonialCharCounter;
  }
}

window.accordion = accordion;
window.mobileNav = mobileNav;
window.breathingApp = breathingApp;
window.testimonialCharCounter = testimonialCharCounter;

// Initialize scroll-based components (they don't need Alpine)
const headerEl = document.getElementById('header');

if (headerEl) {
  scrollHeader()(headerEl);
}

const scrollToTopEl = document.getElementById('scrollToTop');

if (scrollToTopEl) {
  scrollToTop()(scrollToTopEl);
}

const scrollProgressEls =
  document.querySelectorAll<HTMLElement>('.scroll-progress');

scrollProgressEls.forEach((el) => scrollProgress()(el));

// Initialize forms (keep existing logic)
const newsletterFormEl = document.getElementById('newsletterForm');

if (newsletterFormEl) {
  newsletterForm()(newsletterFormEl);
}

const registrationFormEl = document.getElementById('registrationForm');

if (registrationFormEl) {
  registrationForm()(registrationFormEl);
}

const testimonialFormEl = document.getElementById('testimonialForm');

if (testimonialFormEl) {
  testimonialForm()(testimonialFormEl);
}

// Initialize calendar integration
const calendarEl = document.getElementById('addToCalendar');

if (calendarEl) {
  calendarIntegration()(calendarEl);
}

// Initialize event map (Leaflet, lazy-loaded)
const eventMapEls = document.querySelectorAll<HTMLElement>('[data-event-map]');

eventMapEls.forEach((el) => eventMap()(el));

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
