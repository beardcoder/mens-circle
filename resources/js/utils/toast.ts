/**
 * Toast notification utility
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

  const icons = {
    success: '✓',
    error: '✕',
    info: 'i',
    warning: '!',
  };

  const defaultTitles = {
    success: 'Erfolg',
    error: 'Fehler',
    info: 'Information',
    warning: 'Warnung',
  };

  const toast = document.createElement('div');

  toast.className = `toast toast--${type}`;
  toast.setAttribute('role', 'alert');
  toast.setAttribute('aria-live', 'polite');

  const icon = document.createElement('div');

  icon.className = 'toast__icon';
  icon.textContent = icons[type];
  icon.setAttribute('aria-hidden', 'true');

  const content = document.createElement('div');

  content.className = 'toast__content';

  const titleEl = document.createElement('div');

  titleEl.className = 'toast__title';
  titleEl.textContent = title ?? defaultTitles[type];

  const messageEl = document.createElement('div');

  messageEl.className = 'toast__message';
  messageEl.textContent = message;

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
