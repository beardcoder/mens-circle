/**
 * Form Components
 *
 * Three forms — newsletter, registration, testimonial — share a single
 * `createFormHandler` factory. Each `setup*Forms()` export wires that
 * factory up with a form-specific config (URL, payload builder,
 * tracking events).
 *
 * State lives in closure variables; listeners and in-flight requests
 * are cleaned up by Lume when the component unmounts.
 */

import { isValidEmail } from '@/utils/helpers';
import { showToast } from '@/utils/toast';
import {
  TRACKING_EVENTS,
  trackEvent,
  type UmamiEventData,
} from '@/utils/umami';
import { defineComponent, type ComponentContext } from '@beardcoder/lume';
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

interface FormHandlerConfig<TPayload extends Record<string, unknown>> {
  formType: string;
  url: string | ((form: HTMLFormElement) => string);
  buildPayload: (form: HTMLFormElement) => TPayload | null;
  events: {
    abandonFilled: string;
    submit: string;
    success: string;
    error: string;
  };
  submitMeta?: (payload: TPayload) => UmamiEventData;
  onSuccess?: (form: HTMLFormElement) => void;
}

type LumeBindings = Pick<ComponentContext, 'cleanup' | 'on'>;

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

function createFormHandler<TPayload extends Record<string, unknown>>(
  root: HTMLElement,
  config: FormHandlerConfig<TPayload>,
  { cleanup, on }: LumeBindings
): boolean {
  if (!(root instanceof HTMLFormElement)) return false;

  const form = root;
  const submitButton = form.querySelector<HTMLButtonElement>('[type="submit"]');
  const requestController = new AbortController();

  let hasSubmitted = false;
  let hasTrackedAbandon = false;
  let firstInteractionAt: number | null = null;

  const markInteraction = (): void => {
    firstInteractionAt ??= Date.now();
  };

  const trackAbandonIfFilled = (): void => {
    if (hasTrackedAbandon || hasSubmitted || firstInteractionAt === null) {
      return;
    }

    const completion = getFormCompletionState(form);

    if (
      completion.requiredTotal === 0 ||
      completion.requiredFilled < completion.requiredTotal
    ) {
      return;
    }

    hasTrackedAbandon = true;

    trackEvent(config.events.abandonFilled, {
      form: config.formType,
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

  const handleSubmit = async (event: SubmitEvent): Promise<void> => {
    event.preventDefault();
    hasSubmitted = true;

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
        typeof config.url === 'function' ? config.url(form) : config.url;

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify(payload),
        signal: requestController.signal,
      });

      const data = (await response.json()) as ApiResponse;

      if (data.success) {
        form.reset();
        showToast('success', data.message);
        trackEvent(config.events.success);
        config.onSuccess?.(form);
      } else {
        showToast('error', data.message);
        trackEvent(config.events.error, { error: data.message });
      }
    } catch (error) {
      if (requestController.signal.aborted) return;

      const message = error instanceof Error ? error.message : 'Network error';

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
  };

  on(form, 'input', markInteraction);
  on(form, 'change', markInteraction);
  on(form, 'submit', (event) => void handleSubmit(event as SubmitEvent), {
    capture: true,
  });
  on(window, 'pagehide', trackAbandonIfFilled, { capture: true });
  on(window, 'beforeunload', trackAbandonIfFilled, { capture: true });

  cleanup(() => requestController.abort());

  return true;
}

/* ============================================
   Newsletter
   ============================================ */

const newsletterConfig: FormHandlerConfig<{ email: string }> = {
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
};

export const newsletterForm = defineComponent((ctx) => {
  createFormHandler(ctx.root, newsletterConfig, ctx);

  return {};
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

const registrationConfig: FormHandlerConfig<RegistrationPayload> = {
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
};

export const registrationForm = defineComponent((ctx) => {
  createFormHandler(ctx.root, registrationConfig, ctx);

  return {};
});

/* ============================================
   Testimonial — with live char counter binding
   ============================================ */

interface TestimonialPayload extends Record<string, unknown> {
  quote: string;
  author_name: string | null;
  role: string | null;
  email: string;
  privacy: 0 | 1;
}

const testimonialConfig: FormHandlerConfig<TestimonialPayload> = {
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
};

export const testimonialForm = defineComponent((ctx) => {
  const { root, part, on } = ctx;

  if (!(root instanceof HTMLFormElement)) return {};

  if (!createFormHandler(root, testimonialConfig, ctx)) return {};

  const counterEl = part('char-count');
  const textarea = part<HTMLTextAreaElement>('quote-input');

  const update = (): void => {
    counterEl.textContent = String(textarea.value.length);
  };

  on(textarea, 'input', update);
  // Form reset fires after the input is cleared — delay one tick to read the
  // cleared value and zero out the counter.
  on(root, 'reset', () => window.setTimeout(update, 0));
  update();

  return {};
});
