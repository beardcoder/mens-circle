/**
 * Validates an email address format
 */
export function validateEmail(email: string): boolean {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  return re.test(email);
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
