import type { MessageType } from '@/Scripts/types';

/**
 * Validates an email address format
 */
export function validateEmail(email: string): boolean {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  return re.test(email);
}

/**
 * Shows a message in a container element with auto-hide functionality
 */
export function showMessage(
  container: HTMLElement | null,
  message: string,
  type: MessageType
): void {
  if (!container) return;

  container.style.display = 'block';
  container.innerHTML = `<div class="form-message form-message--${type}">${message}</div>`;

  // Auto-hide after 5 seconds
  setTimeout(() => {
    container.innerHTML = '';
    container.style.display = 'none';
  }, 5000);
}

/**
 * Gets the CSRF token from the meta tag
 */
export function getCsrfToken(): string {
  return (
    document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute('content') ?? ''
  );
}
