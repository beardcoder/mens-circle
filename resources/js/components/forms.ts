import { getCsrfToken, showMessage, validateEmail } from '@/utils/helpers';
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

  newsletterForm.addEventListener('submit', (e: Event) => {
    e.preventDefault();
    handleNewsletterSubmit(newsletterForm);
  });
}

async function handleNewsletterSubmit(form: HTMLFormElement): Promise<void> {
  const messageContainer = document.getElementById('newsletterMessage');
  const emailInput = form.querySelector<HTMLInputElement>(
    'input[type="email"]'
  );
  const email = emailInput?.value.trim() ?? '';
  const submitButton = form.querySelector<HTMLButtonElement>(
    'button[type="submit"]'
  );

  if (!submitButton) return;

  if (!validateEmail(email)) {
    showMessage(
      messageContainer,
      'Bitte gib eine gültige E-Mail-Adresse ein.',
      'error'
    );

    return;
  }

  submitButton.disabled = true;
  submitButton.textContent = 'Wird gesendet...';

  try {
    const response = await fetch(window.routes.newsletter, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.routes.csrfToken,
        Accept: 'application/json',
      },
      body: JSON.stringify({ email }),
    });

    const data: ApiResponse = await response.json();

    if (data.success) {
      showMessage(messageContainer, data.message, 'success');
      form.reset();
    } else {
      showMessage(
        messageContainer,
        data.message || 'Ein Fehler ist aufgetreten.',
        'error'
      );
    }
  } catch {
    showMessage(
      messageContainer,
      'Ein Fehler ist aufgetreten. Bitte versuche es später erneut.',
      'error'
    );
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = 'Anmelden';
  }
}

/**
 * Registration Form Handler
 */
function initRegistrationForm(): void {
  const registrationForm = document.getElementById(
    'registrationForm'
  ) as HTMLFormElement | null;

  if (!registrationForm) return;

  registrationForm.addEventListener('submit', (e: Event) => {
    e.preventDefault();
    handleRegistrationSubmit(registrationForm);
  });
}

async function handleRegistrationSubmit(form: HTMLFormElement): Promise<void> {
  const messageContainer = document.getElementById('registrationMessage');
  const formData = new FormData(form);
  const submitButton = form.querySelector<HTMLButtonElement>(
    'button[type="submit"]'
  );

  if (!submitButton) return;

  const firstName = (formData.get('first_name') as string)?.trim();
  const lastName = (formData.get('last_name') as string)?.trim();
  const email = (formData.get('email') as string)?.trim();
  const phoneNumber = (formData.get('phone_number') as string)?.trim() || null;
  const privacy = form.querySelector<HTMLInputElement>(
    'input[name="privacy"]'
  )?.checked;
  const eventId = formData.get('event_id') as string;

  // Validation
  if (!firstName || !lastName) {
    showMessage(
      messageContainer,
      'Bitte fülle alle Pflichtfelder aus.',
      'error'
    );

    return;
  }

  if (!validateEmail(email)) {
    showMessage(
      messageContainer,
      'Bitte gib eine gültige E-Mail-Adresse ein.',
      'error'
    );

    return;
  }

  if (!privacy) {
    showMessage(
      messageContainer,
      'Bitte bestätige die Datenschutzerklärung.',
      'error'
    );

    return;
  }

  submitButton.disabled = true;
  submitButton.textContent = 'Wird gesendet...';

  try {
    const response = await fetch(window.routes.eventRegister, {
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

    const data: ApiResponse = await response.json();

    if (data.success) {
      showMessage(messageContainer, data.message, 'success');
      form.reset();
    } else {
      showMessage(
        messageContainer,
        data.message || 'Ein Fehler ist aufgetreten.',
        'error'
      );
    }
  } catch {
    showMessage(
      messageContainer,
      'Ein Fehler ist aufgetreten. Bitte versuche es später erneut.',
      'error'
    );
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = 'Verbindlich anmelden';
  }
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

  testimonialForm.addEventListener('submit', (e: Event) => {
    e.preventDefault();
    handleTestimonialSubmit(testimonialForm);
  });
}

async function handleTestimonialSubmit(form: HTMLFormElement): Promise<void> {
  const messageContainer = document.getElementById('formMessage');
  const formData = new FormData(form);
  const submitButton = form.querySelector<HTMLButtonElement>(
    'button[type="submit"]'
  );

  if (!submitButton) return;

  const submitText = submitButton.querySelector<HTMLSpanElement>('.btn__text');
  const submitLoader =
    submitButton.querySelector<HTMLSpanElement>('.btn__loader');

  const quote = (formData.get('quote') as string)?.trim();
  const authorName = (formData.get('author_name') as string)?.trim() || null;
  const role = (formData.get('role') as string)?.trim() || null;
  const email = (formData.get('email') as string)?.trim();
  const privacy = form.querySelector<HTMLInputElement>(
    'input[name="privacy"]'
  )?.checked;

  // Validation
  if (!quote || quote.length < 10) {
    showMessage(
      messageContainer,
      'Bitte teile deine Erfahrung mit uns (mindestens 10 Zeichen).',
      'error'
    );

    return;
  }

  if (!validateEmail(email)) {
    showMessage(
      messageContainer,
      'Bitte gib eine gültige E-Mail-Adresse ein.',
      'error'
    );

    return;
  }

  if (!privacy) {
    showMessage(
      messageContainer,
      'Bitte bestätige die Datenschutzerklärung.',
      'error'
    );

    return;
  }

  submitButton.disabled = true;

  if (submitText) {
    submitText.style.display = 'none';
  }

  if (submitLoader) {
    submitLoader.style.display = 'inline-block';
  }

  const submitUrl = form.getAttribute('data-submit-url') ?? '';

  try {
    const response = await fetch(submitUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
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

    const data: ApiResponse = await response.json();

    if (data.success) {
      showMessage(messageContainer, data.message, 'success');
      form.reset();

      // Reset character counter
      const charCount = document.getElementById('charCount');

      if (charCount) {
        charCount.textContent = '0';
      }
    } else {
      showMessage(
        messageContainer,
        data.message || 'Ein Fehler ist aufgetreten.',
        'error'
      );
    }
  } catch {
    showMessage(
      messageContainer,
      'Ein Fehler ist aufgetreten. Bitte versuche es später erneut.',
      'error'
    );
  } finally {
    submitButton.disabled = false;

    if (submitText) {
      submitText.style.display = 'inline';
    }

    if (submitLoader) {
      submitLoader.style.display = 'none';
    }
  }
}
