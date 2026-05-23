/**
 * Form Components — Alpine.js data factories
 *
 * Three forms (newsletter, registration, testimonial) share the same
 * lifecycle: abandon tracking, JSON submission, toast feedback, retryable
 * cleanup. `createFormHandler` is the shared factory; each export below is
 * a thin adapter that supplies form-specific validation and tracking.
 *
 * Listeners are managed with a single `AbortController` so Alpine's
 * `destroy()` releases everything in one call — no manual bookkeeping.
 */

import { isValidEmail } from '@/utils/helpers';
import { showToast } from '@/utils/toast';
import {
  TRACKING_EVENTS,
  trackEvent,
  type UmamiEventData,
} from '@/utils/umami';
import type { ApiResponse } from '@/types';
import type { AlpineMagics } from '@/types/alpine';

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

interface AbandonTrackingOptions {
  form: HTMLFormElement;
  eventName: string;
  formType: string;
  signal: AbortSignal;
}

interface FormHandlerConfig<TPayload> {
  /** Where to POST */
  url: string | ((form: HTMLFormElement) => string);
  /** Build the JSON body. Return `null` to abort submission (after surfacing a toast). */
  buildPayload: (form: HTMLFormElement) => TPayload | null;
  /** Tracking events fired around submission */
  events: {
    abandonFilled: string;
    submit: string;
    success: string;
    error: string;
  };
  /** Logical form name used in tracking metadata */
  formType: string;
  /** Optional submit-time tracking metadata derived from the payload */
  submitMeta?: (payload: TPayload) => UmamiEventData;
  /** Side-effects on success (e.g. reset counters) */
  onSuccess?: () => void;
}

interface FormHandlerInstance {
  charCount?: number;
  init(): void;
  destroy(): void;
}

const SUBMIT_LABEL = 'Wird gesendet...';

function isFieldFilled(field: FormFieldElement): boolean {
  if (field instanceof HTMLInputElement) {
    if (field.type === 'checkbox' || field.type === 'radio') {
      return field.checked;
    }
  }

  return field.value.trim().length > 0;
}

function isRequiredFieldComplete(field: FormFieldElement): boolean {
  if (!field.required) return true;

  return isFieldFilled(field) && field.validity.valid;
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

function setupAbandonTracking({
  form,
  eventName,
  formType,
  signal,
}: AbandonTrackingOptions): { markSubmitted: () => void } {
  let hasSubmitted = false;
  let hasTracked = false;
  let firstInteractionAt: number | null = null;

  const markInteraction = (): void => {
    firstInteractionAt ??= Date.now();
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

  form.addEventListener('input', markInteraction, { signal });
  form.addEventListener('change', markInteraction, { signal });
  form.addEventListener('submit', markSubmitted, { capture: true, signal });
  window.addEventListener('pagehide', trackAbandonIfFilled, {
    capture: true,
    signal,
  });
  window.addEventListener('beforeunload', trackAbandonIfFilled, {
    capture: true,
    signal,
  });

  return { markSubmitted };
}

async function postJson(
  url: string,
  body: Record<string, unknown>,
  signal: AbortSignal
): Promise<ApiResponse> {
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: JSON.stringify(body),
    signal,
  });

  return (await response.json()) as ApiResponse;
}

function createFormHandler<TPayload extends Record<string, unknown>>(
  config: FormHandlerConfig<TPayload>
): () => FormHandlerInstance {
  return function handler(): FormHandlerInstance {
    const controller = new AbortController();

    return {
      init(this: AlpineMagics): void {
        const form = this.$el;

        if (!(form instanceof HTMLFormElement)) return;

        const { signal } = controller;

        setupAbandonTracking({
          form,
          eventName: config.events.abandonFilled,
          formType: config.formType,
          signal,
        });

        const submitButton =
          form.querySelector<HTMLButtonElement>('[type="submit"]');

        form.addEventListener(
          'submit',
          async (event) => {
            event.preventDefault();

            const payload = config.buildPayload(form);

            if (payload === null) return;

            trackEvent(config.events.submit, config.submitMeta?.(payload));

            const originalLabel = submitButton?.textContent ?? '';

            if (submitButton) {
              submitButton.disabled = true;
              submitButton.textContent = SUBMIT_LABEL;
            }

            try {
              const url =
                typeof config.url === 'function'
                  ? config.url(form)
                  : config.url;
              const data = await postJson(url, payload, signal);

              if (data.success) {
                form.reset();
                showToast('success', data.message);
                trackEvent(config.events.success);
                config.onSuccess?.();
              } else {
                showToast('error', data.message);
                trackEvent(config.events.error, { error: data.message });
              }
            } catch (error) {
              if (signal.aborted) return;

              const message =
                error instanceof Error ? error.message : 'Network error';

              showToast(
                'error',
                'Ein Fehler ist aufgetreten. Bitte versuche es erneut.'
              );
              trackEvent(config.events.error, { error: message });
            } finally {
              if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalLabel;
              }
            }
          },
          { signal }
        );
      },

      destroy(): void {
        controller.abort();
      },
    };
  };
}

/* ============================================
   Newsletter
   ============================================ */

export const newsletterForm = createFormHandler<{ email: string }>({
  formType: 'newsletter',
  url: () => window.routes.newsletter,
  events: {
    abandonFilled: TRACKING_EVENTS.NEWSLETTER_ABANDON_FILLED,
    submit: TRACKING_EVENTS.NEWSLETTER_SUBMIT,
    success: TRACKING_EVENTS.NEWSLETTER_SUCCESS,
    error: TRACKING_EVENTS.NEWSLETTER_ERROR,
  },
  buildPayload(form) {
    const email = String(new FormData(form).get('email') ?? '').trim();

    if (!isValidEmail(email)) {
      showToast('error', 'Bitte gib eine gültige E-Mail-Adresse ein.');

      return null;
    }

    return { email };
  },
});

/* ============================================
   Event registration
   ============================================ */

interface RegistrationPayload extends Record<string, unknown> {
  event_id: string;
  first_name: string;
  last_name: string;
  email: string;
  phone_number: string | null;
  privacy: 0 | 1;
}

export const registrationForm = createFormHandler<RegistrationPayload>({
  formType: 'event-registration',
  url: () => window.routes.eventRegister,
  events: {
    abandonFilled: TRACKING_EVENTS.EVENT_REGISTRATION_ABANDON_FILLED,
    submit: TRACKING_EVENTS.EVENT_REGISTRATION_SUBMIT,
    success: TRACKING_EVENTS.EVENT_REGISTRATION_SUCCESS,
    error: TRACKING_EVENTS.EVENT_REGISTRATION_ERROR,
  },
  submitMeta: (payload) => ({
    event_id: payload.event_id,
    has_phone: payload.phone_number ? 'yes' : 'no',
  }),
  buildPayload(form) {
    const data = new FormData(form);
    const firstName = String(data.get('first_name') ?? '').trim();
    const lastName = String(data.get('last_name') ?? '').trim();
    const email = String(data.get('email') ?? '').trim();
    const phone = String(data.get('phone_number') ?? '').trim();
    const eventId = String(data.get('event_id') ?? '');
    const privacy = form.querySelector<HTMLInputElement>(
      'input[name="privacy"]'
    )?.checked;

    if (!firstName || !lastName) {
      showToast('error', 'Bitte fülle alle Pflichtfelder aus.');

      return null;
    }

    if (!isValidEmail(email)) {
      showToast('error', 'Bitte gib eine gültige E-Mail-Adresse ein.');

      return null;
    }

    if (!privacy) {
      showToast('error', 'Bitte bestätige die Datenschutzerklärung.');

      return null;
    }

    return {
      event_id: eventId,
      first_name: firstName,
      last_name: lastName,
      email,
      phone_number: phone || null,
      privacy: 1,
    };
  },
});

/* ============================================
   Testimonial
   ============================================ */

interface TestimonialPayload extends Record<string, unknown> {
  quote: string;
  author_name: string | null;
  role: string | null;
  email: string;
  privacy: 0 | 1;
}

const baseTestimonial = createFormHandler<TestimonialPayload>({
  formType: 'testimonial',
  url: (form) => form.dataset.submitUrl ?? '',
  events: {
    abandonFilled: TRACKING_EVENTS.TESTIMONIAL_ABANDON_FILLED,
    submit: TRACKING_EVENTS.TESTIMONIAL_SUBMIT,
    success: TRACKING_EVENTS.TESTIMONIAL_SUCCESS,
    error: TRACKING_EVENTS.TESTIMONIAL_ERROR,
  },
  submitMeta: (payload) => ({
    has_name: payload.author_name ? 'yes' : 'no',
    has_role: payload.role ? 'yes' : 'no',
    char_count: payload.quote.length,
  }),
  buildPayload(form) {
    const data = new FormData(form);
    const quote = String(data.get('quote') ?? '').trim();
    const authorName = String(data.get('author_name') ?? '').trim();
    const role = String(data.get('role') ?? '').trim();
    const email = String(data.get('email') ?? '').trim();
    const privacy = form.querySelector<HTMLInputElement>(
      'input[name="privacy"]'
    )?.checked;

    if (!quote || quote.length < 10) {
      showToast(
        'error',
        'Bitte teile deine Erfahrung mit uns (mindestens 10 Zeichen).'
      );

      return null;
    }

    if (!isValidEmail(email)) {
      showToast('error', 'Bitte gib eine gültige E-Mail-Adresse ein.');

      return null;
    }

    if (!privacy) {
      showToast('error', 'Bitte bestätige die Datenschutzerklärung.');

      return null;
    }

    return {
      quote,
      author_name: authorName || null,
      role: role || null,
      email,
      privacy: 1,
    };
  },
});

/**
 * Wraps the base testimonial factory to expose a reactive char counter and
 * bind the quote textarea's `input` event to keep it in sync.
 */
export function testimonialForm(): FormHandlerInstance & { charCount: number } {
  const base = baseTestimonial();
  const counterController = new AbortController();

  return {
    ...base,
    charCount: 0,
    init(this: AlpineMagics & { charCount: number }) {
      base.init.call(this);

      const textarea = this.$el.querySelector<HTMLTextAreaElement>('#quote');

      textarea?.addEventListener(
        'input',
        () => {
          this.charCount = textarea.value.length;
        },
        { signal: counterController.signal }
      );
    },
    destroy(this: { charCount: number }) {
      counterController.abort();
      base.destroy();
      this.charCount = 0;
    },
  };
}
