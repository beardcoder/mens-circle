/**
 * Toast notification utility — Tailwind-styled, no custom CSS dependency.
 */

type ToastType = 'success' | 'error' | 'info' | 'warning';

const ICONS: Record<ToastType, string> = {
  success: '✓',
  error: '✕',
  info: 'i',
  warning: '!',
};

const DEFAULT_TITLES: Record<ToastType, string> = {
  success: 'Erfolg',
  error: 'Fehler',
  info: 'Information',
  warning: 'Warnung',
};

const ACCENT: Record<ToastType, string> = {
  success: 'border-l-[oklch(58%_0.15_145deg)]',
  error: 'border-l-[oklch(55%_0.18_25deg)]',
  info: 'border-l-[oklch(60%_0.12_240deg)]',
  warning: 'border-l-[oklch(68%_0.16_75deg)]',
};

let container: HTMLElement | null = null;

function ensureContainer(): HTMLElement {
  if (container) return container;
  container = document.createElement('div');
  container.className =
    'fixed bottom-6 right-6 z-[1500] flex flex-col gap-3 pointer-events-none';
  container.setAttribute('aria-live', 'polite');
  document.body.appendChild(container);

  return container;
}

export function showToast(
  type: ToastType,
  message: string,
  title?: string
): void {
  const root = ensureContainer();

  const toast = document.createElement('div');

  toast.className = [
    'pointer-events-auto flex max-w-sm gap-3 rounded-xl border-l-4 bg-[var(--bg)] p-4 shadow-lg',
    'translate-x-4 opacity-0 transition-all duration-300 ease-[var(--ease-settle)]',
    ACCENT[type],
  ].join(' ');
  toast.setAttribute('role', 'alert');

  const icon = document.createElement('div');

  icon.className =
    'grid h-8 w-8 shrink-0 place-items-center rounded-full bg-[var(--bg-alt)] font-bold';
  icon.textContent = ICONS[type];
  icon.setAttribute('aria-hidden', 'true');

  const content = document.createElement('div');

  content.className = 'flex flex-col gap-0.5 text-sm';

  const titleEl = document.createElement('div');

  titleEl.className = 'font-semibold text-[var(--fg)]';
  titleEl.textContent = title ?? DEFAULT_TITLES[type];

  const messageEl = document.createElement('div');

  messageEl.className = 'text-[var(--fg-muted)]';
  messageEl.textContent = message;

  content.append(titleEl, messageEl);
  toast.append(icon, content);
  root.appendChild(toast);

  requestAnimationFrame(() => {
    toast.classList.remove('translate-x-4', 'opacity-0');
  });

  const remove = () => toast.remove();

  window.setTimeout(() => {
    toast.classList.add('translate-x-4', 'opacity-0');
    toast.addEventListener('transitionend', remove, { once: true });
    window.setTimeout(remove, 400);
  }, 5000);
}
