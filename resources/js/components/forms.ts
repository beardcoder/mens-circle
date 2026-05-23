/**
 * Form Components
 *
 * Three forms (newsletter, registration, testimonial) share the same
 * lifecycle: abandon tracking, JSON submission, toast feedback. A single
 * `FormHandler` class drives all three; each `setup*Forms()` exports
 * wires it up with a form-specific config object.
 *
 * Listeners are managed via the shared `ReactiveHost` AbortController so
 * tearing down releases everything in one call.
 */

import { isValidEmail } from '@/utils/helpers';
import { showToast } from '@/utils/toast';
import {
  TRACKING_EVENTS,
  trackEvent,
  type UmamiEventData,
} from '@/utils/umami';
import { mountAll, ReactiveHost } from '@/lib/reactive-host';
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
  /** Logical form name used in tracking metadata */
  formType: string;
  /** Where to POST — may depend on the form element (e.g. data-submit-url) */
  url: string | ((form: HTMLFormElement) => string);
  /** Build the JSON body. Return `null` to abort submission. */
  buildPayload: (form: HTMLFormElement) => TPayload | null;
  /** Tracking events fired around submission */
  events: {
    abandonFilled: string;
    submit: string;
    success: string;
    error: string;
  };
  /** Optional submit-time tracking metadata derived from the payload */
  submitMeta?: (payload: TPayload) => UmamiEventData;
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

class FormHandler<
  TPayload extends Record<string, unknown>,
> extends ReactiveHost {
  private readonly form: HTMLFormElement;
  private readonly config: FormHandlerConfig<TPayload>;
  private hasSubmitted = false;
  private hasTrackedAbandon = false;
  private firstInteractionAt: number | null = null;

  public constructor(
    form: HTMLFormElement,
    config: FormHandlerConfig<TPayload>
  ) {
    super(form);
    this.form = form;
    this.config = config;
  }

  protected setup(): void {
    const submitButton = this.query<HTMLButtonElement>('[type="submit"]');

    this.on(this.form, 'input', () => this.markInteraction());
    this.on(this.form, 'change', () => this.markInteraction());
    this.on(
      this.form,
      'submit',
      (event) => {
        void this.handleSubmit(event, submitButton);
      },
      { capture: true }
    );

    this.onWindow('pagehide', () => this.trackAbandonIfFilled(), {
      capture: true,
    });
    this.onWindow('beforeunload', () => this.trackAbandonIfFilled(), {
      capture: true,
    });
  }

  private markInteraction(): void {
    this.firstInteractionAt ??= Date.now();
  }

  private trackAbandonIfFilled(): void {
    if (
      this.hasTrackedAbandon ||
      this.hasSubmitted ||
      this.firstInteractionAt === null
    ) {
      return;
    }

    const completion = getFormCompletionState(this.form);

    if (
      completion.requiredTotal === 0 ||
      completion.requiredFilled < completion.requiredTotal
    ) {
      return;
    }

    this.hasTrackedAbandon = true;

    trackEvent(this.config.events.abandonFilled, {
      form: this.config.formType,
      required_filled: completion.requiredFilled,
      required_total: completion.requiredTotal,
      required_completion_pct: Math.round(
        (completion.requiredFilled / completion.requiredTotal) * 100
      ),
      filled_fields: completion.filledFields,
      total_fields: completion.totalFields,
      seconds_since_first_input: Math.round(
        (Date.now() - this.firstInteractionAt) / 1000
      ),
      page: window.location.pathname,
    });
  }

  private async handleSubmit(
    event: SubmitEvent,
    submitButton: HTMLButtonElement | null
  ): Promise<void> {
    event.preventDefault();

    this.hasSubmitted = true;

    const payload = this.config.buildPayload(this.form);

    if (payload === null) return;

    trackEvent(this.config.events.submit, this.config.submitMeta?.(payload));

    const originalLabel = submitButton?.textContent ?? '';

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = SUBMIT_LABEL;
    }

    try {
      const url =
        typeof this.config.url === 'function'
          ? this.config.url(this.form)
          : this.config.url;

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify(payload),
        signal: this.signal,
      });

      const data = (await response.json()) as ApiResponse;

      if (data.success) {
        this.form.reset();
        showToast('success', data.message);
        trackEvent(this.config.events.success);
        this.onSuccess?.();
      } else {
        showToast('error', data.message);
        trackEvent(this.config.events.error, { error: data.message });
      }
    } catch (error) {
      if (this.signal.aborted) return;

      const message = error instanceof Error ? error.message : 'Network error';

      showToast(
        'error',
        'Ein Fehler ist aufgetreten. Bitte versuche es erneut.'
      );
      trackEvent(this.config.events.error, { error: message });
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = originalLabel;
      }
    }
  }

  /** Subclasses override to react after a successful submission. */
  protected onSuccess?(): void;
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

export function setupNewsletterForms(): void {
  mountAll(
    '[data-component="newsletter-form"]',
    (el) => new FormHandler(el as HTMLFormElement, newsletterConfig)
  );
}

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

export function setupRegistrationForms(): void {
  mountAll(
    '[data-component="registration-form"]',
    (el) => new FormHandler(el as HTMLFormElement, registrationConfig)
  );
}

/* ============================================
   Testimonial — extends the base with a live char counter
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

class TestimonialFormHandler extends FormHandler<TestimonialPayload> {
  private counterEl: HTMLElement | null = null;
  private textarea: HTMLTextAreaElement | null = null;

  public constructor(form: HTMLFormElement) {
    super(form, testimonialConfig);
  }

  protected override setup(): void {
    super.setup();

    this.counterEl = this.query('[data-ref="char-count"]');
    this.textarea = this.query<HTMLTextAreaElement>('#quote');

    if (this.textarea) {
      this.on(this.textarea, 'input', () => this.updateCount());
      this.updateCount();
    }
  }

  protected override onSuccess(): void {
    this.updateCount();
  }

  private updateCount(): void {
    if (!this.counterEl || !this.textarea) return;
    this.counterEl.textContent = String(this.textarea.value.length);
  }
}

export function setupTestimonialForms(): void {
  mountAll(
    '[data-component="testimonial-form"]',
    (el) => new TestimonialFormHandler(el as HTMLFormElement)
  );
}
