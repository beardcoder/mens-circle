/**
 * Form enhancements for Extbase forms with Hotwired Turbo support.
 * Forms work seamlessly with Turbo Drive for SPA-like navigation and submissions.
 */

import { validateEmail } from '../utils/helpers';
import { TRACKING_EVENTS, trackEvent } from '../utils/umami';

function setValidationError(
  input: HTMLInputElement | HTMLTextAreaElement,
  message: string
): void {
  input.setCustomValidity(message);
  input.reportValidity();
}

function clearValidationError(
  input: HTMLInputElement | HTMLTextAreaElement
): void {
  input.setCustomValidity('');
}

/**
 * Newsletter form with Turbo support
 * Turbo automatically handles form submissions as AJAX requests
 */
export function useNewsletterForm(): void {
  const form = document.querySelector<HTMLFormElement>('.newsletter__form');
  if (!form) {
    return;
  }

  // Use turbo:submit-start for validation before Turbo submits
  form.addEventListener('turbo:submit-start', (event) => {
    const emailInput = form.querySelector<HTMLInputElement>('input[name="email"]');
    const email = emailInput?.value.trim() ?? '';

    if (!emailInput || !validateEmail(email)) {
      if (emailInput) {
        setValidationError(emailInput, 'Bitte gib eine gültige E-Mail-Adresse ein.');
      }
      event.preventDefault();
      return;
    }

    clearValidationError(emailInput);
    trackEvent(TRACKING_EVENTS.NEWSLETTER_SUBMIT);
  });

  // Handle successful submission
  form.addEventListener('turbo:submit-end', (event: Event) => {
    const customEvent = event as CustomEvent;
    if (customEvent.detail.success) {
      // Form submitted successfully - Turbo will handle the response
      form.reset();
    }
  });
}

/**
 * Event registration form with Turbo support
 * Validates all required fields before allowing Turbo to submit
 */
export function useRegistrationForm(): void {
  const form = document.querySelector<HTMLFormElement>('.event-register__form');
  if (!form) {
    return;
  }

  form.addEventListener('turbo:submit-start', (event) => {
    const eventInput = form.querySelector<HTMLInputElement>('input[name="event"]');
    const firstNameInput = form.querySelector<HTMLInputElement>('input[name="firstName"]');
    const lastNameInput = form.querySelector<HTMLInputElement>('input[name="lastName"]');
    const emailInput = form.querySelector<HTMLInputElement>('input[name="email"]');
    const phoneInput = form.querySelector<HTMLInputElement>('input[name="phoneNumber"]');
    const privacyInput = form.querySelector<HTMLInputElement>('input[name="privacy"]');

    const firstName = firstNameInput?.value.trim() ?? '';
    const lastName = lastNameInput?.value.trim() ?? '';
    const email = emailInput?.value.trim() ?? '';

    if (!firstNameInput || firstName === '') {
      if (firstNameInput) {
        setValidationError(firstNameInput, 'Bitte gib deinen Vornamen ein.');
      }
      event.preventDefault();
      return;
    }
    clearValidationError(firstNameInput);

    if (!lastNameInput || lastName === '') {
      if (lastNameInput) {
        setValidationError(lastNameInput, 'Bitte gib deinen Nachnamen ein.');
      }
      event.preventDefault();
      return;
    }
    clearValidationError(lastNameInput);

    if (!emailInput || !validateEmail(email)) {
      if (emailInput) {
        setValidationError(emailInput, 'Bitte gib eine gültige E-Mail-Adresse ein.');
      }
      event.preventDefault();
      return;
    }
    clearValidationError(emailInput);

    if (!privacyInput?.checked) {
      event.preventDefault();
      return;
    }

    trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_SUBMIT, {
      event_id: eventInput?.value ?? '',
      has_phone: phoneInput?.value.trim() ? 'yes' : 'no',
    });
  });

  // Handle successful submission
  form.addEventListener('turbo:submit-end', (event: Event) => {
    const customEvent = event as CustomEvent;
    if (customEvent.detail.success) {
      // Turbo will handle the redirect to success page
      // No need to manually reset form as user will be redirected
    }
  });
}

/**
 * Testimonial form with Turbo support
 * Validates content quality before submission
 */
export function useTestimonialForm(): void {
  const form = document.querySelector<HTMLFormElement>('.testimonial-form');
  if (!form) {
    return;
  }

  form.addEventListener('turbo:submit-start', (event) => {
    const quoteInput = form.querySelector<HTMLTextAreaElement>('textarea[name="quote"]');
    const nameInput = form.querySelector<HTMLInputElement>('input[name="authorName"]');
    const roleInput = form.querySelector<HTMLInputElement>('input[name="role"]');
    const emailInput = form.querySelector<HTMLInputElement>('input[name="email"]');
    const privacyInput = form.querySelector<HTMLInputElement>('input[name="privacy"]');

    const quote = quoteInput?.value.trim() ?? '';
    const email = emailInput?.value.trim() ?? '';

    if (!quoteInput || quote.length < 10) {
      if (quoteInput) {
        setValidationError(quoteInput, 'Bitte teile deine Erfahrung mit mindestens 10 Zeichen.');
      }
      event.preventDefault();
      return;
    }
    clearValidationError(quoteInput);

    if (!emailInput || !validateEmail(email)) {
      if (emailInput) {
        setValidationError(emailInput, 'Bitte gib eine gültige E-Mail-Adresse ein.');
      }
      event.preventDefault();
      return;
    }
    clearValidationError(emailInput);

    if (!privacyInput?.checked) {
      event.preventDefault();
      return;
    }

    trackEvent(TRACKING_EVENTS.TESTIMONIAL_SUBMIT, {
      has_name: nameInput?.value.trim() ? 'yes' : 'no',
      has_role: roleInput?.value.trim() ? 'yes' : 'no',
      char_count: quote.length,
    });
  });

  // Handle successful submission
  form.addEventListener('turbo:submit-end', (event: Event) => {
    const customEvent = event as CustomEvent;
    if (customEvent.detail.success) {
      // Form submitted successfully - Turbo will handle the response
      form.reset();
    }
  });
}
