/**
 * Form Composables - Modern Functional Pattern
 * Handles all form interactions with validation and submission
 */

import { validateEmail } from '@/utils/helpers';
import { useForm } from '@/composables';

/**
 * Newsletter form composable
 * Handles newsletter subscription with validation
 */
export function useNewsletterForm(): void {
  const form = document.getElementById('newsletterForm') as HTMLFormElement;

  if (!form) return;

  useForm(form, {
    onSubmit: async (formData) => {
      const email = formData.get('email') as string;

      if (!validateEmail(email)) {
        throw new Error('Bitte gib eine gültige E-Mail-Adresse ein.');
      }

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
  });
}

/**
 * Event registration form composable
 * Handles event registration with validation
 */
export function useRegistrationForm(): void {
  const form = document.getElementById('registrationForm') as HTMLFormElement;

  if (!form) return;

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

      return fetch((globalThis as any).routes.eventRegister, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': (globalThis as any).routes.csrfToken,
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
  });
}

/**
 * Testimonial form composable
 * Handles testimonial submission with character counter
 */
export function useTestimonialForm(): void {
  const form = document.getElementById('testimonialForm') as HTMLFormElement;

  if (!form) return;

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
    },
  });
}
