/**
 * Scroll-triggered micro animations.
 */

let scrollAnimationObserver: IntersectionObserver | null = null;

const revealThreshold = 0.12;
const revealRootMargin = '0px 0px -10% 0px';

/**
 * Normalize a raw delay value from markup into a CSS time string.
 *
 * @param rawDelay The raw `data-delay` attribute value.
 *
 * @returns A normalized CSS time value or `null` when the input is invalid.
 */
function normalizeDelayValue(rawDelay: string | null): string | null {
  if (rawDelay === null) {
    return null;
  }

  const trimmedDelay = rawDelay.trim();

  if (trimmedDelay === '') {
    return null;
  }

  if (/^\d+(\.\d+)?m?s$/.test(trimmedDelay)) {
    return trimmedDelay;
  }

  const numericDelay = Number(trimmedDelay);

  if (Number.isFinite(numericDelay) && numericDelay >= 0) {
    return `${numericDelay}ms`;
  }

  return null;
}

/**
 * Read an element's `data-delay` attribute and expose it as `--animate-delay`.
 *
 * @param element The element that may define a stagger delay.
 */
function applyAnimationDelay(element: HTMLElement): void {
  const delay = normalizeDelayValue(element.dataset.delay ?? null);

  if (delay !== null) {
    element.style.setProperty('--animate-delay', delay);

    return;
  }

  element.style.removeProperty('--animate-delay');
}

/**
 * Trigger the reveal transition for a single observed element.
 *
 * @param element The element entering the viewport.
 */
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
      threshold: revealThreshold,
      rootMargin: revealRootMargin,
    }
  );

  elements.forEach((element) => {
    if (element.classList.contains('is-in-view')) {
      return;
    }

    scrollAnimationObserver?.observe(element);
  });
}
