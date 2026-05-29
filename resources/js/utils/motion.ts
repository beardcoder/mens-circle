/**
 * Motion — scroll-triggered reveals
 *
 * A dependency-free IntersectionObserver toggles `.is-in` on `[data-reveal]`
 * elements as they enter the viewport; CSS transitions (utilities/_motion.css)
 * carry the motion. IntersectionObserver is rock-solid across browsers and
 * mobile — no scroll-timeline quirks, nothing freezes mid-animation at the
 * page bottom.
 *
 * Markup:
 *   <h2 data-reveal>                       fade + rise
 *   <p  data-reveal="blur">                headline burn-in
 *   <a  data-reveal="up" data-reveal-delay="120">
 *   <img data-reveal="zoom" data-reveal-duration="900">
 *   <li data-reveal="up" data-reveal-repeat>   replays on every entry
 *
 * Auto-stagger the direct children of a group:
 *   <ul data-reveal-group>                 default step (90ms)
 *   <ul data-reveal-group="120">           custom step in ms
 *     <li data-reveal="up">…</li>
 *     <li data-reveal="up">…</li>
 *   </ul>
 */

function prepare(elements: HTMLElement[]): void {
  for (const group of document.querySelectorAll<HTMLElement>(
    '[data-reveal-group]'
  )) {
    const step = Number(group.dataset.revealGroup);
    const children = group.querySelectorAll<HTMLElement>(
      ':scope > [data-reveal]'
    );

    children.forEach((child, index) => {
      child.style.setProperty('--reveal-index', String(index));

      if (Number.isFinite(step) && step > 0) {
        child.style.setProperty('--reveal-step', `${step}ms`);
      }
    });
  }

  for (const el of elements) {
    if (el.dataset.revealDelay !== undefined) {
      el.style.setProperty(
        '--reveal-base',
        `${Number(el.dataset.revealDelay)}ms`
      );
    }

    if (el.dataset.revealDuration !== undefined) {
      el.style.setProperty(
        '--reveal-duration',
        `${Number(el.dataset.revealDuration)}ms`
      );
    }
  }
}

export function initMotion(): void {
  const elements = Array.from(
    document.querySelectorAll<HTMLElement>('[data-reveal]')
  );

  if (elements.length === 0) {
    return;
  }

  prepare(elements);

  // Reduced motion: reveal everything immediately, skip observation.
  if (globalThis.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    for (const el of elements) {
      el.classList.add('is-in');
    }

    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        const el = entry.target as HTMLElement;

        if (entry.isIntersecting) {
          reveal(el);

          if (el.dataset.revealRepeat === undefined) {
            observer.unobserve(el);
          }
        } else if (el.dataset.revealRepeat !== undefined) {
          el.classList.remove('is-in');
        }
      }
    },
    { rootMargin: '0px 0px -12% 0px', threshold: 0 }
  );

  for (const el of elements) {
    observer.observe(el);
  }
}

/**
 * Reveal a single element. `will-change` is promoted only for the duration
 * of the transition and dropped on completion — keeping the compositor lean
 * on mobile, where dozens of permanently-promoted layers cost real memory.
 */
function reveal(el: HTMLElement): void {
  el.style.willChange = 'transform, opacity';
  el.classList.add('is-in');

  let settled = false;
  const release = (): void => {
    if (settled) {
      return;
    }

    settled = true;
    el.style.willChange = '';
    el.removeEventListener('transitionend', onEnd);
    clearTimeout(fallback);
  };

  const onEnd = (event: TransitionEvent): void => {
    if (event.target === el && event.propertyName === 'opacity') {
      release();
    }
  };

  el.addEventListener('transitionend', onEnd);
  // Safety net if no transition runs (e.g. element already at rest state).
  const fallback = globalThis.setTimeout(release, 1600);
}
