/**
 * Progressive form enhancement via fetch + Fluid-rendered HTML fragments.
 *
 * Forms with `data-enhance` are intercepted: on submit we POST via fetch
 * with an XHR header. The server renders a Fluid result template and
 * short-circuits the page pipeline (PropagateResponseException), so we
 * receive only the result fragment.
 *
 * On success the nearest `[data-enhance-container]` is swapped with the
 * result content (Turbo Frame-like behaviour). On error a toast is shown
 * and the form stays in place so the user can correct their input.
 *
 * Without JS the normal PRG flow still works — pure progressive enhancement.
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
  const container = form.closest<HTMLElement>('[data-enhance-container]');

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

        // On success: swap the container content (like a Turbo Frame)
        if (severity === 'success' && container) {
          container.innerHTML = html;
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
