/**
 * Form Components — Alpine.js data factories
 */

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

  return field.value.trim().length > 0 && field.validity.valid;
}

function getFormCompletionState(form: HTMLFormElement): FormCompletionState {
  const fields = getTrackableFields(form);
  const requiredFields = fields.filter((f) => f.required);

  return {
    requiredFilled: requiredFields.filter(isRequiredFieldComplete).length,
    requiredTotal: requiredFields.length,
    filledFields: fields.filter(isFieldFilled).length,
    totalFields: fields.length,
  };
}

function setupAbandonTracking(
  form: HTMLFormElement,
  eventName: string,
  formType: string,
  registerCleanup: (fn: () => void) => void
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

    trackEvent(eventName, {
      form: formType,
      required_filled: completion.requiredFilled,
      required_total: completion.requiredTotal,
      required_completion_pct: Math.round(
        (completion.requiredFilled / completion.requiredTotal) * 100
      ),
      filled_fields: completion.filledFields,
      total_fields: completion.totalFields,
      seconds_since_first_input: Math.round(
        (Date.now() - firstInteractionAt) / 1000
      ),
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

  registerCleanup(() => {
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

export function newsletterForm() {
  return {
    _cleanup: [] as Array<() => void>,

    init() {
      const form = (this as unknown as { $el: HTMLFormElement }).$el;

      if (form.tagName !== 'FORM') return;

      setupAbandonTracking(
        form,
        TRACKING_EVENTS.NEWSLETTER_ABANDON_FILLED,
        'newsletter',
        (fn) => this._cleanup.push(fn)
      );

      const onSubmit = (e: Event): void => {
        e.preventDefault();

        const formData = new FormData(form);
        const email = formData.get('email') as string;

        if (!validateEmail(email)) {
          showToast('error', 'Bitte gib eine gültige E-Mail-Adresse ein.');

          return;
        }

        trackEvent(TRACKING_EVENTS.NEWSLETTER_SUBMIT);

        void submitFormRequest(
          form,
          window.routes.newsletter,
          { email },
          () => trackEvent(TRACKING_EVENTS.NEWSLETTER_SUCCESS),
          (err) =>
            trackEvent(TRACKING_EVENTS.NEWSLETTER_ERROR, { error: err.message })
        );
      };

      form.addEventListener('submit', onSubmit);
      this._cleanup.push(() => form.removeEventListener('submit', onSubmit));
    },

    destroy(): void {
      this._cleanup.forEach((fn) => fn());
      this._cleanup = [];
    },
  };
}

export function registrationForm() {
  return {
    _cleanup: [] as Array<() => void>,

    init() {
      const form = (this as unknown as { $el: HTMLFormElement }).$el;

      if (form.tagName !== 'FORM') return;

      setupAbandonTracking(
        form,
        TRACKING_EVENTS.EVENT_REGISTRATION_ABANDON_FILLED,
        'event-registration',
        (fn) => this._cleanup.push(fn)
      );

      const onSubmit = (e: Event): void => {
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

        void submitFormRequest(
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
          (err) =>
            trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_ERROR, {
              error: err.message,
            })
        );
      };

      form.addEventListener('submit', onSubmit);
      this._cleanup.push(() => form.removeEventListener('submit', onSubmit));
    },

    destroy(): void {
      this._cleanup.forEach((fn) => fn());
      this._cleanup = [];
    },
  };
}

export function testimonialForm() {
  return {
    charCount: 0,
    _cleanup: [] as Array<() => void>,

    init() {
      const form = (this as unknown as { $el: HTMLFormElement }).$el;

      if (form.tagName !== 'FORM') return;

      setupAbandonTracking(
        form,
        TRACKING_EVENTS.TESTIMONIAL_ABANDON_FILLED,
        'testimonial',
        (fn) => this._cleanup.push(fn)
      );

      const quoteTextarea = form.querySelector<HTMLTextAreaElement>('#quote');

      if (quoteTextarea) {
        const handleInput = (): void => {
          this.charCount = quoteTextarea.value.length;
        };

        quoteTextarea.addEventListener('input', handleInput);
        this._cleanup.push(() =>
          quoteTextarea.removeEventListener('input', handleInput)
        );
      }

      const submitUrl =
        (this as unknown as { $el: HTMLElement }).$el.dataset.submitUrl ?? '';

      const onSubmit = (e: Event): void => {
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

        void submitFormRequest(
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
            this.charCount = 0;
            trackEvent(TRACKING_EVENTS.TESTIMONIAL_SUCCESS);
          },
          (err) =>
            trackEvent(TRACKING_EVENTS.TESTIMONIAL_ERROR, {
              error: err.message,
            })
        );
      };

      form.addEventListener('submit', onSubmit);
      this._cleanup.push(() => form.removeEventListener('submit', onSubmit));
    },

    destroy(): void {
      this._cleanup.forEach((fn) => fn());
      this._cleanup = [];
    },
  };
}
