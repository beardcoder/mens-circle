/**
 * Progressive form enhancement via fetch + HTML fragments.
 * Forms with `data-enhance` are intercepted: on submit we fetch the
 * same action URL and display the server-rendered result fragment
 * as a toast. Without JS the normal PRG flow still works.
 */

import { showToast } from '../composables';

type ToastType = 'success' | 'error' | 'info' | 'warning';

const SEVERITY_MAP: Record<string, ToastType> = {
  success: 'success',
  ok: 'success',
  warning: 'warning',
  info: 'info',
  error: 'error',
};

export function useFormEnhancer(): void {
  document
    .querySelectorAll<HTMLFormElement>('form[data-enhance]')
    .forEach(enhanceForm);
}

function enhanceForm(form: HTMLFormElement): void {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const btn = form.querySelector<HTMLButtonElement>('[type="submit"]');
    const originalText = btn?.textContent ?? '';

    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Wird gesendet…';
    }

    try {
      const response = await fetch(form.action, {
        method: form.method || 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });

      const html = await response.text();
      const doc = new DOMParser().parseFromString(html, 'text/html');
      const result = doc.querySelector('[data-form-result]');

      if (result) {
        const severity = result.getAttribute('data-form-result') ?? 'error';
        const message = result.textContent?.trim() ?? '';
        const toastType = SEVERITY_MAP[severity] ?? 'error';

        showToast(toastType, message);

        if (severity === 'success' || severity === 'ok') {
          form.reset();
        }
      }
    } catch {
      showToast(
        'error',
        'Ein Fehler ist aufgetreten. Bitte versuche es erneut.',
      );
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.textContent = originalText;
      }
    }
  });
}
