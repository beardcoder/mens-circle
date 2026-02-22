/**
 * Form Composables - Modern Functional Pattern
 * Handles all form interactions with validation and submission
 */

import { validateEmail } from '@/utils/helpers';
import { useForm } from '@/composables';
import { TRACKING_EVENTS, trackEvent } from '@/utils/umami';

type FormFieldElement =
  | HTMLInputElement
  | HTMLTextAreaElement
  | HTMLSelectElement;

interface FormCompletionState {
  requiredFilled: number;
  requiredTotal: number;
  filledFields: number;
  totalFields: number;
}

function getTrackableFields(form: HTMLFormElement): FormFieldElement[] {
  return Array.from(
    form.querySelectorAll<FormFieldElement>('input, textarea, select')
  ).filter((field) => {
    if (!field.name || field.disabled) {
      return false;
    }

    if (field instanceof HTMLInputElement && field.type === 'hidden') {
      return false;
    }

    return true;
  });
}

function isFieldFilled(field: FormFieldElement): boolean {
  if (field instanceof HTMLInputElement) {
    if (field.type === 'checkbox' || field.type === 'radio') {
      return field.checked;
    }

    return field.value.trim().length > 0;
  }

  if (field instanceof HTMLSelectElement) {
    return field.value.trim().length > 0;
  }

  return field.value.trim().length > 0;
}

function isRequiredFieldComplete(field: FormFieldElement): boolean {
  if (!field.required) {
    return true;
  }

  if (field instanceof HTMLInputElement) {
    if (field.type === 'checkbox' || field.type === 'radio') {
      return field.checked && field.validity.valid;
    }

    return field.value.trim().length > 0 && field.validity.valid;
  }

  if (field instanceof HTMLSelectElement) {
    return field.value.trim().length > 0 && field.validity.valid;
  }

  return field.value.trim().length > 0 && field.validity.valid;
}

function getFormCompletionState(form: HTMLFormElement): FormCompletionState {
  const fields = getTrackableFields(form);
  const requiredFields = fields.filter((field) => field.required);

  const filledFields = fields.filter((field) => isFieldFilled(field)).length;
  const requiredFilled = requiredFields.filter((field) =>
    isRequiredFieldComplete(field)
  ).length;

  return {
    requiredFilled,
    requiredTotal: requiredFields.length,
    filledFields,
    totalFields: fields.length,
  };
}

function useFilledFormAbandonTracking(
  form: HTMLFormElement,
  eventName: string,
  formType: string
): void {
  let hasSubmitted = false;
  let hasTracked = false;
  let firstInteractionAt: number | null = null;

  const markInteraction = (): void => {
    if (firstInteractionAt === null) {
      firstInteractionAt = Date.now();
    }
  };

  const markSubmitted = (): void => {
    hasSubmitted = true;
  };

  const trackAbandonIfFilled = (): void => {
    if (hasTracked || hasSubmitted || firstInteractionAt === null) {
      return;
    }

    const completion = getFormCompletionState(form);

    if (
      completion.requiredTotal === 0 ||
      completion.requiredFilled < completion.requiredTotal
    ) {
      return;
    }

    hasTracked = true;

    const requiredCompletionPercent = Math.round(
      (completion.requiredFilled / completion.requiredTotal) * 100
    );
    const secondsSinceFirstInput = Math.round(
      (Date.now() - firstInteractionAt) / 1000
    );

    trackEvent(eventName, {
      form: formType,
      required_filled: completion.requiredFilled,
      required_total: completion.requiredTotal,
      required_completion_pct: requiredCompletionPercent,
      filled_fields: completion.filledFields,
      total_fields: completion.totalFields,
      seconds_since_first_input: secondsSinceFirstInput,
      page: window.location.pathname,
    });
  };

  form.addEventListener('input', markInteraction);
  form.addEventListener('change', markInteraction);
  form.addEventListener('submit', markSubmitted, { capture: true });

  window.addEventListener('pagehide', trackAbandonIfFilled, { capture: true });
  window.addEventListener('beforeunload', trackAbandonIfFilled, {
    capture: true,
  });
}

/**
 * Newsletter form composable
 * Handles newsletter subscription with validation
 */
export function useNewsletterForm(): void {
  const form = document.getElementById('newsletterForm') as HTMLFormElement;

  if (!form) return;

  useFilledFormAbandonTracking(
    form,
    TRACKING_EVENTS.NEWSLETTER_ABANDON_FILLED,
    'newsletter'
  );

  useForm(form, {
    onSubmit: async (formData) => {
      const email = formData.get('email') as string;

      if (!validateEmail(email)) {
        throw new Error('Bitte gib eine gültige E-Mail-Adresse ein.');
      }

      // Track newsletter submission
      trackEvent(TRACKING_EVENTS.NEWSLETTER_SUBMIT);

      return fetch(window.routes.newsletter, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': window.routes.csrfToken,
          Accept: 'application/json',
        },
        body: JSON.stringify({ email }),
      });
    },
    onSuccess: () => {
      // Track successful newsletter subscription
      trackEvent(TRACKING_EVENTS.NEWSLETTER_SUCCESS);
    },
    onError: (error) => {
      // Track newsletter subscription error
      trackEvent(TRACKING_EVENTS.NEWSLETTER_ERROR, {
        error: error.message,
      });
    },
  });
}

/**
 * Event registration form composable
 * Handles event registration with validation
 */
export function useRegistrationForm(): void {
  const form = document.getElementById('registrationForm') as HTMLFormElement;

  if (!form) return;

  useFilledFormAbandonTracking(
    form,
    TRACKING_EVENTS.EVENT_REGISTRATION_ABANDON_FILLED,
    'event-registration'
  );

  useForm(form, {
    onSubmit: async (formData) => {
      const firstName = (formData.get('first_name') as string)?.trim();
      const lastName = (formData.get('last_name') as string)?.trim();
      const email = (formData.get('email') as string)?.trim();
      const phoneNumber =
        (formData.get('phone_number') as string)?.trim() || null;
      const privacy = form.querySelector<HTMLInputElement>(
        'input[name="privacy"]'
      )?.checked;
      const eventId = formData.get('event_id') as string;

      if (!firstName || !lastName) {
        throw new Error('Bitte fülle alle Pflichtfelder aus.');
      }

      if (!validateEmail(email)) {
        throw new Error('Bitte gib eine gültige E-Mail-Adresse ein.');
      }

      if (!privacy) {
        throw new Error('Bitte bestätige die Datenschutzerklärung.');
      }

      // Track event registration submission
      trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_SUBMIT, {
        event_id: eventId,
        has_phone: phoneNumber ? 'yes' : 'no',
      });

      return fetch(window.routes.eventRegister, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': window.routes.csrfToken,
          Accept: 'application/json',
        },
        body: JSON.stringify({
          event_id: eventId,
          first_name: firstName,
          last_name: lastName,
          email,
          phone_number: phoneNumber,
          privacy: privacy ? 1 : 0,
        }),
      });
    },
    onSuccess: () => {
      // Track successful event registration
      trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_SUCCESS);
    },
    onError: (error) => {
      // Track event registration error
      trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_ERROR, {
        error: error.message,
      });
    },
  });
}

/**
 * Testimonial form composable
 * Handles testimonial submission with character counter
 */
export function useTestimonialForm(): void {
  const form = document.getElementById('testimonialForm') as HTMLFormElement;

  if (!form) return;

  useFilledFormAbandonTracking(
    form,
    TRACKING_EVENTS.TESTIMONIAL_ABANDON_FILLED,
    'testimonial'
  );

  const quoteTextarea = form.querySelector<HTMLTextAreaElement>('#quote');
  const charCount = document.getElementById('charCount');

  if (quoteTextarea && charCount) {
    quoteTextarea.addEventListener('input', () => {
      charCount.textContent = String(quoteTextarea.value.length);
    });
  }

  const submitUrl = form.dataset.submitUrl ?? '';

  useForm(form, {
    onSubmit: async (formData) => {
      const quote = (formData.get('quote') as string)?.trim();
      const authorName =
        (formData.get('author_name') as string)?.trim() || null;
      const role = (formData.get('role') as string)?.trim() || null;
      const email = (formData.get('email') as string)?.trim();
      const privacy = form.querySelector<HTMLInputElement>(
        'input[name="privacy"]'
      )?.checked;

      if (!quote || quote.length < 10) {
        throw new Error(
          'Bitte teile deine Erfahrung mit uns (mindestens 10 Zeichen).'
        );
      }

      if (!validateEmail(email)) {
        throw new Error('Bitte gib eine gültige E-Mail-Adresse ein.');
      }

      if (!privacy) {
        throw new Error('Bitte bestätige die Datenschutzerklärung.');
      }

      // Track testimonial submission
      trackEvent(TRACKING_EVENTS.TESTIMONIAL_SUBMIT, {
        has_name: authorName ? 'yes' : 'no',
        has_role: role ? 'yes' : 'no',
        char_count: quote.length,
      });

      return fetch(submitUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': window.routes.csrfToken,
          Accept: 'application/json',
        },
        body: JSON.stringify({
          quote,
          author_name: authorName,
          role,
          email,
          privacy: privacy ? 1 : 0,
        }),
      });
    },
    onSuccess: () => {
      if (charCount) {
        charCount.textContent = '0';
      }

      // Track successful testimonial submission
      trackEvent(TRACKING_EVENTS.TESTIMONIAL_SUCCESS);
    },
    onError: (error) => {
      // Track testimonial submission error
      trackEvent(TRACKING_EVENTS.TESTIMONIAL_ERROR, {
        error: error.message,
      });
    },
  });
}
