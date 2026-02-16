/**
 * Composables for Men's Circle App
 * Modern, reusable functionality with better separation of concerns
 */

import { animate, inView, scroll, stagger } from 'motion';

export interface AnimationOptions {
  threshold?: number;
  amount?: number;
  rootMargin?: string;
}

/**
 * Modern Intersection Observer wrapper using Motion One
 */
export function useIntersectionObserver(options: AnimationOptions = {}): void {
  const { threshold = 0.1, amount = 0.3 } = options;

  // Fade in animations for sections
  const sections = document.querySelectorAll<HTMLElement>(
    '[data-animate="fade-in"]'
  );

  sections.forEach((section) => {
    inView(
      section,
      () => {
        animate(
          section,
          { opacity: [0, 1], y: [20, 0] },
          { duration: 0.6, easing: 'ease-out' }
        );
      },
      { amount }
    );
  });

  // Staggered animations for lists
  const staggeredLists = document.querySelectorAll<HTMLElement>(
    '[data-animate="stagger"]'
  );

  staggeredLists.forEach((list) => {
    const items = list.querySelectorAll<HTMLElement>('[data-animate-item]');

    inView(
      list,
      () => {
        animate(
          items,
          { opacity: [0, 1], y: [20, 0] },
          { duration: 0.5, delay: stagger(0.1), easing: 'ease-out' }
        );
      },
      { amount: threshold }
    );
  });

  // Scale animations
  const scaleElements = document.querySelectorAll<HTMLElement>(
    '[data-animate="scale"]'
  );

  scaleElements.forEach((el) => {
    inView(
      el,
      () => {
        animate(
          el,
          { opacity: [0, 1], scale: [0.9, 1] },
          { duration: 0.5, easing: 'ease-out' }
        );
      },
      { amount }
    );
  });
}

/**
 * Parallax scroll effects using Motion One
 */
export function useParallax(): void {
  const parallaxElements =
    document.querySelectorAll<HTMLElement>('[data-parallax]');

  parallaxElements.forEach((el) => {
    const speed = Number.parseFloat(el.dataset.parallax || '0.5');

    scroll(animate(el, { y: [0, -100 * speed] }), {
      target: el,
      offset: ['start end', 'end start'],
    });
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

  // Animate in
  animate(
    toast,
    { opacity: [0, 1], y: [-20, 0], scale: [0.95, 1] },
    { duration: 0.4, easing: [0.16, 1, 0.3, 1] }
  );

  // Auto-dismiss after 5 seconds
  setTimeout(() => {
    animate(
      toast,
      { opacity: 0, y: -20, scale: 0.95 },
      { duration: 0.3, easing: 'ease-in' }
    ).finished.then(() => toast.remove());
  }, 5000);
}
