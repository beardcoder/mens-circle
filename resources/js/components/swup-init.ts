/**
 * Swup 4 — AJAX page transitions for the public site.
 *
 * - Replaces the `<main id="main">` container without a full reload.
 * - Re-initializes components on the new DOM.
 * - Resets the Umami tracker on every page view so per-page metrics
 *   (scroll depth, time on page, etc.) start fresh.
 * - Uses native View Transitions where supported via `native: true`.
 */

import Swup from 'swup';
import { initUmamiKit } from '@/utils/umami-kit';
import {
  newsletterForm,
  registrationForm,
  testimonialForm,
} from '@/components/forms';
import { calendarIntegration } from '@/components/calendar';
import { eventMap } from '@/components/event-map';
import { scrollProgress } from '@/components/navigation';

/**
 * Reinitialize components after Swup replaces content
 * This is called after the #main container is replaced
 */
function reinitializeComponents(): void {
  // Forms
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

  // Calendar integration
  const calendarEl = document.getElementById('addToCalendar');

  if (calendarEl) {
    calendarIntegration()(calendarEl);
  }

  // Event map (Leaflet, lazy-loaded)
  const eventMapEls =
    document.querySelectorAll<HTMLElement>('[data-event-map]');

  eventMapEls.forEach((el) => eventMap()(el));

  // Scroll utilities (header and scroll-to-top should already be initialized globally)
  // But scroll progress bars in new content need initialization
  const scrollProgressEls =
    document.querySelectorAll<HTMLElement>('.scroll-progress');

  scrollProgressEls.forEach((el) => scrollProgress()(el));

  // Header visibility state might need update after new content
  const headerEl = document.getElementById('header');

  if (headerEl) {
    const hasHero = Boolean(document.querySelector('.hero'));

    document.body.classList.toggle('has-hero', hasHero);
    document.body.classList.toggle('no-hero', !hasHero);
  }
}

export function initSwup(): Swup {
  const swup = new Swup({
    containers: ['#main'],
    native: true,
    cache: true,
    linkSelector:
      'a[href]:not([data-no-swup]):not([download]):not([target="_blank"])',
  });

  swup.hooks.on('content:replace', () => {
    reinitializeComponents();
  });

  swup.hooks.on('page:view', () => {
    window.umamiTracker?.destroy();
    initUmamiKit();
  });

  return swup;
}
