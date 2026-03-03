/**
 * Form Components
 * Newsletter, registration, and testimonial forms using stitch-js
 */

import { defineComponent } from '@stitch';
import { validateEmail } from '@/utils/helpers';
import { showToast } from '@/utils/toast';
import { TRACKING_EVENTS, trackEvent } from '@/utils/umami';
import type { ApiResponse } from '@/types';

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
    if (!field.name || field.disabled) return false;

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
  if (!field.required) return true;

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

function setupAbandonTracking(
  form: HTMLFormElement,
  eventName: string,
  formType: string,
  onDestroy: (fn: () => void) => void
): { markSubmitted: () => void } {
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
    if (hasTracked || hasSubmitted || firstInteractionAt === null) return;

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

  onDestroy(() => {
    form.removeEventListener('input', markInteraction);
    form.removeEventListener('change', markInteraction);
    form.removeEventListener('submit', markSubmitted);
    window.removeEventListener('pagehide', trackAbandonIfFilled);
    window.removeEventListener('beforeunload', trackAbandonIfFilled);
  });

  return { markSubmitted };
}

async function submitFormRequest(
  form: HTMLFormElement,
  url: string,
  body: Record<string, unknown>,
  onSuccess?: () => void,
  onError?: (error: Error) => void
): Promise<void> {
  const submitButton = form.querySelector<HTMLButtonElement>('[type="submit"]');
  const originalButtonText = submitButton?.textContent ?? '';

  if (submitButton) {
    submitButton.disabled = true;
    submitButton.textContent = 'Wird gesendet...';
  }

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify(body),
    });

    const data: ApiResponse = await response.json();

    if (data.success) {
      form.reset();
      showToast('success', data.message);
      onSuccess?.();
    } else {
      showToast('error', data.message);
      onError?.(new Error(data.message));
    }
  } catch {
    showToast('error', 'Ein Fehler ist aufgetreten. Bitte versuche es erneut.');
    onError?.(new Error('Network error'));
  } finally {
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.textContent = originalButtonText;
    }
  }
}

// eslint-disable-next-line @typescript-eslint/no-empty-object-type
interface NewsletterFormOptions {}

/**
 * Newsletter form component
 * Attach to #newsletterForm
 */
export const newsletterForm = defineComponent<NewsletterFormOptions>(
  {},
  (ctx) => {
    const form = ctx.el as HTMLFormElement;

    if (form.tagName !== 'FORM') return;

    setupAbandonTracking(
      form,
      TRACKING_EVENTS.NEWSLETTER_ABANDON_FILLED,
      'newsletter',
      (fn) => ctx.onDestroy(fn)
    );

    ctx.on('submit', (e) => {
      e.preventDefault();

      const formData = new FormData(form);
      const email = formData.get('email') as string;

      if (!validateEmail(email)) {
        showToast('error', 'Bitte gib eine gültige E-Mail-Adresse ein.');

        return;
      }

      trackEvent(TRACKING_EVENTS.NEWSLETTER_SUBMIT);

      submitFormRequest(
        form,
        window.routes.newsletter,
        { email },
        () => trackEvent(TRACKING_EVENTS.NEWSLETTER_SUCCESS),
        (error) =>
          trackEvent(TRACKING_EVENTS.NEWSLETTER_ERROR, {
            error: error.message,
          })
      );
    });
  }
);

// eslint-disable-next-line @typescript-eslint/no-empty-object-type
interface RegistrationFormOptions {}

/**
 * Event registration form component
 * Attach to #registrationForm
 */
export const registrationForm = defineComponent<RegistrationFormOptions>(
  {},
  (ctx) => {
    const form = ctx.el as HTMLFormElement;

    if (form.tagName !== 'FORM') return;

    setupAbandonTracking(
      form,
      TRACKING_EVENTS.EVENT_REGISTRATION_ABANDON_FILLED,
      'event-registration',
      (fn) => ctx.onDestroy(fn)
    );

    ctx.on('submit', (e) => {
      e.preventDefault();

      const formData = new FormData(form);
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
        showToast('error', 'Bitte fülle alle Pflichtfelder aus.');

        return;
      }

      if (!validateEmail(email)) {
        showToast('error', 'Bitte gib eine gültige E-Mail-Adresse ein.');

        return;
      }

      if (!privacy) {
        showToast('error', 'Bitte bestätige die Datenschutzerklärung.');

        return;
      }

      trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_SUBMIT, {
        event_id: eventId,
        has_phone: phoneNumber ? 'yes' : 'no',
      });

      submitFormRequest(
        form,
        window.routes.eventRegister,
        {
          event_id: eventId,
          first_name: firstName,
          last_name: lastName,
          email,
          phone_number: phoneNumber,
          privacy: privacy ? 1 : 0,
        },
        () => trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_SUCCESS),
        (error) =>
          trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_ERROR, {
            error: error.message,
          })
      );
    });
  }
);

// eslint-disable-next-line @typescript-eslint/no-empty-object-type
interface TestimonialFormOptions {}

/**
 * Testimonial form component
 * Attach to #testimonialForm
 */
export const testimonialForm = defineComponent<TestimonialFormOptions>(
  {},
  (ctx) => {
    const form = ctx.el as HTMLFormElement;

    if (form.tagName !== 'FORM') return;

    setupAbandonTracking(
      form,
      TRACKING_EVENTS.TESTIMONIAL_ABANDON_FILLED,
      'testimonial',
      (fn) => ctx.onDestroy(fn)
    );

    const quoteTextarea = ctx.query<HTMLTextAreaElement>('#quote');
    const charCount = document.getElementById('charCount');

    if (quoteTextarea && charCount) {
      const handleInput = (): void => {
        charCount.textContent = String(quoteTextarea.value.length);
      };

      quoteTextarea.addEventListener('input', handleInput);
      ctx.onDestroy(() =>
        quoteTextarea.removeEventListener('input', handleInput)
      );
    }

    const submitUrl = ctx.el.dataset.submitUrl ?? '';

    ctx.on('submit', (e) => {
      e.preventDefault();

      const formData = new FormData(form);
      const quote = (formData.get('quote') as string)?.trim();
      const authorName =
        (formData.get('author_name') as string)?.trim() || null;
      const role = (formData.get('role') as string)?.trim() || null;
      const email = (formData.get('email') as string)?.trim();
      const privacy = form.querySelector<HTMLInputElement>(
        'input[name="privacy"]'
      )?.checked;

      if (!quote || quote.length < 10) {
        showToast(
          'error',
          'Bitte teile deine Erfahrung mit uns (mindestens 10 Zeichen).'
        );

        return;
      }

      if (!validateEmail(email)) {
        showToast('error', 'Bitte gib eine gültige E-Mail-Adresse ein.');

        return;
      }

      if (!privacy) {
        showToast('error', 'Bitte bestätige die Datenschutzerklärung.');

        return;
      }

      trackEvent(TRACKING_EVENTS.TESTIMONIAL_SUBMIT, {
        has_name: authorName ? 'yes' : 'no',
        has_role: role ? 'yes' : 'no',
        char_count: quote.length,
      });

      submitFormRequest(
        form,
        submitUrl,
        {
          quote,
          author_name: authorName,
          role,
          email,
          privacy: privacy ? 1 : 0,
        },
        () => {
          if (charCount) {
            charCount.textContent = '0';
          }

          trackEvent(TRACKING_EVENTS.TESTIMONIAL_SUCCESS);
        },
        (error) =>
          trackEvent(TRACKING_EVENTS.TESTIMONIAL_ERROR, {
            error: error.message,
          })
      );
    });
  }
);
