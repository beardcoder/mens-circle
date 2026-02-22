/**
 * Composables for Men's Circle App
 * Reusable functionality with minimal runtime overhead
 */

import type { ApiResponse } from '@/types';

/**
 * Enhanced form handling with better UX
 */
export interface FormOptions {
  onSubmit: (data: FormData) => Promise<Response>;
  onSuccess?: (data: ApiResponse) => void;
  onError?: (error: Error) => void;
}

export function useForm(
  formElement: HTMLFormElement,
  options: FormOptions
): void {
  const submitButton =
    formElement.querySelector<HTMLButtonElement>('[type="submit"]');
  const originalButtonText = submitButton?.textContent ?? '';

  formElement.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Wird gesendet...';
    }

    const formData = new FormData(formElement);

    try {
      const response = await options.onSubmit(formData);
      const data = await response.json();

      if (data.success) {
        formElement.reset();
        options.onSuccess?.(data);
        showToast('success', data.message);
      } else {
        options.onError?.(new Error(data.message));
        showToast('error', data.message);
      }
    } catch (error) {
      options.onError?.(error as Error);
      showToast(
        'error',
        'Ein Fehler ist aufgetreten. Bitte versuche es erneut.'
      );
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      }
    }
  });
}

/**
 * Modern toast notifications with enhanced design
 */
export function showToast(
  type: 'success' | 'error' | 'info' | 'warning',
  message: string,
  title?: string
): void {
  const TOAST_VISIBLE_CLASS = 'toast--visible';
  const TOAST_HIDING_CLASS = 'toast--hiding';
  const TOAST_LIFETIME_MS = 5000;
  const TOAST_FALLBACK_REMOVE_MS = 400;

  // Icon symbols for each type
  const icons = {
    success: '✓',
    error: '✕',
    info: 'i',
    warning: '!',
  };

  // Default titles if none provided
  const defaultTitles = {
    success: 'Erfolg',
    error: 'Fehler',
    info: 'Information',
    warning: 'Warnung',
  };

  // Create toast container
  const toast = document.createElement('div');

  toast.className = `toast toast--${type}`;
  toast.setAttribute('role', 'alert');
  toast.setAttribute('aria-live', 'polite');

  // Create icon element
  const icon = document.createElement('div');

  icon.className = 'toast__icon';
  icon.textContent = icons[type];
  icon.setAttribute('aria-hidden', 'true');

  // Create content container
  const content = document.createElement('div');

  content.className = 'toast__content';

  // Create title element
  const titleEl = document.createElement('div');

  titleEl.className = 'toast__title';
  titleEl.textContent = title ?? defaultTitles[type];

  // Create message element
  const messageEl = document.createElement('div');

  messageEl.className = 'toast__message';
  messageEl.textContent = message;

  // Assemble the toast
  content.appendChild(titleEl);
  content.appendChild(messageEl);
  toast.appendChild(icon);
  toast.appendChild(content);

  document.body.appendChild(toast);

  requestAnimationFrame(() => {
    toast.classList.add(TOAST_VISIBLE_CLASS);
  });

  const removeToast = (): void => {
    if (toast.isConnected) {
      toast.remove();
    }
  };

  window.setTimeout(() => {
    toast.classList.remove(TOAST_VISIBLE_CLASS);
    toast.classList.add(TOAST_HIDING_CLASS);
    toast.addEventListener('transitionend', removeToast, { once: true });
    window.setTimeout(removeToast, TOAST_FALLBACK_REMOVE_MS);
  }, TOAST_LIFETIME_MS);
}
