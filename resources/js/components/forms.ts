import { validateEmail } from '@/utils/helpers';
import { useForm, showToast } from '@/composables';
import type { ApiResponse } from '@/types';

export function initForms(): void {
  initNewsletterForm();
  initRegistrationForm();
  initTestimonialForm();
}

/**
 * Newsletter Form Handler
 */
function initNewsletterForm(): void {
  const newsletterForm = document.getElementById(
    'newsletterForm'
  ) as HTMLFormElement | null;

  if (!newsletterForm) return;

  useForm(newsletterForm, {
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
 * Registration Form Handler
 */
function initRegistrationForm(): void {
  const registrationForm = document.getElementById(
    'registrationForm'
  ) as HTMLFormElement | null;

  if (!registrationForm) return;

  useForm(registrationForm, {
    onSubmit: async (formData) => {
      const firstName = (formData.get('first_name') as string)?.trim();
      const lastName = (formData.get('last_name') as string)?.trim();
      const email = (formData.get('email') as string)?.trim();
      const phoneNumber = (formData.get('phone_number') as string)?.trim() || null;
      const privacy = registrationForm.querySelector<HTMLInputElement>(
        'input[name="privacy"]'
      )?.checked;
      const eventId = formData.get('event_id') as string;

      // Validation
      if (!firstName || !lastName) {
        throw new Error('Bitte fülle alle Pflichtfelder aus.');
      }

      if (!validateEmail(email)) {
        throw new Error('Bitte gib eine gültige E-Mail-Adresse ein.');
      }

      if (!privacy) {
        throw new Error('Bitte bestätige die Datenschutzerklärung.');
      }

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
  });
}

/**
 * Testimonial Form Handler
 */
function initTestimonialForm(): void {
  const testimonialForm = document.getElementById(
    'testimonialForm'
  ) as HTMLFormElement | null;

  if (!testimonialForm) return;

  // Character counter
  const quoteTextarea =
    testimonialForm.querySelector<HTMLTextAreaElement>('#quote');
  const charCount = document.getElementById('charCount');

  if (quoteTextarea && charCount) {
    quoteTextarea.addEventListener('input', () => {
      charCount.textContent = String(quoteTextarea.value.length);
    });
  }

  const submitUrl = testimonialForm.getAttribute('data-submit-url') ?? '';

  useForm(testimonialForm, {
    onSubmit: async (formData) => {
      const quote = (formData.get('quote') as string)?.trim();
      const authorName = (formData.get('author_name') as string)?.trim() || null;
      const role = (formData.get('role') as string)?.trim() || null;
      const email = (formData.get('email') as string)?.trim();
      const privacy = testimonialForm.querySelector<HTMLInputElement>(
        'input[name="privacy"]'
      )?.checked;

      // Validation
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
      // Reset character counter
      const charCount = document.getElementById('charCount');
      if (charCount) {
        charCount.textContent = '0';
      }
    },
  });
}

