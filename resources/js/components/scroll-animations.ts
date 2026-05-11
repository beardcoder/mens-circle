/**
 * Scroll-triggered micro animations.
 */

let scrollAnimationObserver: IntersectionObserver | null = null;

function normalizeDelayValue(rawDelay: string | null): string | null {
  if (rawDelay === null) {
    return null;
  }

  const trimmedDelay = rawDelay.trim();

  if (trimmedDelay === '') {
    return null;
  }

  if (/^-?\d+(\.\d+)?m?s$/.test(trimmedDelay)) {
    return trimmedDelay;
  }

  const numericDelay = Number(trimmedDelay);

  if (Number.isFinite(numericDelay)) {
    return `${numericDelay}ms`;
  }

  return null;
}

function applyAnimationDelay(element: HTMLElement): void {
  const delay = normalizeDelayValue(element.dataset.delay ?? null);

  if (delay !== null) {
    element.style.setProperty('--animate-delay', delay);

    return;
  }

  element.style.removeProperty('--animate-delay');
}

function revealElement(element: HTMLElement): void {
  element.classList.add('is-in-view');
}

export function initScrollAnimations(root: ParentNode = document): void {
  scrollAnimationObserver?.disconnect();

  const elements = Array.from(
    root.querySelectorAll<HTMLElement>('.animate-on-scroll')
  );

  if (elements.length === 0) {
    scrollAnimationObserver = null;

    return;
  }

  elements.forEach(applyAnimationDelay);

  const prefersReducedMotion =
    'matchMedia' in globalThis &&
    globalThis.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (!('IntersectionObserver' in globalThis) || prefersReducedMotion) {
    elements.forEach(revealElement);
    scrollAnimationObserver = null;

    return;
  }

  scrollAnimationObserver = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting || !(entry.target instanceof HTMLElement)) {
          return;
        }

        revealElement(entry.target);
        observer.unobserve(entry.target);
      });
    },
    {
      threshold: 0.12,
      rootMargin: '0px 0px -10% 0px',
    }
  );

  elements.forEach((element) => {
    if (element.classList.contains('is-in-view')) {
      return;
    }

    scrollAnimationObserver?.observe(element);
  });
}
