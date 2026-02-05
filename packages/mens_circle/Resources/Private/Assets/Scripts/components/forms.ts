/**
 * Form Tracking - Hooks into FluidForms events for analytics
 *
 * FluidForms handles all form submission, validation, and UX.
 * This module only adds Umami event tracking via FluidForms custom events.
 */

import { TRACKING_EVENTS, trackEvent } from '@/Scripts/utils/umami';

/**
 * Newsletter form tracking
 */
export function useNewsletterForm(): void {
  const form = document.querySelector<HTMLFormElement>('.newsletter__form[data-fluid-form]');
  if (!form) return;

  form.addEventListener('ff:success', () => {
    trackEvent(TRACKING_EVENTS.NEWSLETTER_SUCCESS);
  });

  form.addEventListener('ff:error', ((e: CustomEvent) => {
    trackEvent(TRACKING_EVENTS.NEWSLETTER_ERROR, {
      error: e.detail?.message || 'unknown',
    });
  }) as EventListener);

  // Track submission attempt
  form.addEventListener('submit', () => {
    trackEvent(TRACKING_EVENTS.NEWSLETTER_SUBMIT);
  });
}

/**
 * Event registration form tracking
 */
export function useRegistrationForm(): void {
  const form = document.querySelector<HTMLFormElement>('.event-register__form[data-fluid-form]');
  if (!form) return;

  form.addEventListener('ff:success', () => {
    trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_SUCCESS);
  });

  form.addEventListener('ff:error', ((e: CustomEvent) => {
    trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_ERROR, {
      error: e.detail?.message || 'unknown',
    });
  }) as EventListener);

  form.addEventListener('submit', () => {
    trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_SUBMIT);
  });
}

/**
 * Testimonial form tracking with character counter
 */
export function useTestimonialForm(): void {
  const form = document.querySelector<HTMLFormElement>('[data-fluid-form]#testimonialForm');
  if (!form) return;

  const quoteTextarea = form.querySelector<HTMLTextAreaElement>('#quote');
  const charCount = document.getElementById('charCount');

  if (quoteTextarea && charCount) {
    quoteTextarea.addEventListener('input', () => {
      charCount.textContent = String(quoteTextarea.value.length);
    });
  }

  form.addEventListener('ff:success', () => {
    if (charCount) charCount.textContent = '0';
    trackEvent(TRACKING_EVENTS.TESTIMONIAL_SUCCESS);
  });

  form.addEventListener('ff:error', ((e: CustomEvent) => {
    trackEvent(TRACKING_EVENTS.TESTIMONIAL_ERROR, {
      error: e.detail?.message || 'unknown',
    });
  }) as EventListener);

  form.addEventListener('submit', () => {
    trackEvent(TRACKING_EVENTS.TESTIMONIAL_SUBMIT, {
      char_count: quoteTextarea?.value.length || 0,
    });
  });
}
