/**
 * Animate On Scroll — lightweight, dependency-free
 *
 * Mirrors the ergonomics of the AOS library (michalsnik.github.io/aos)
 * without the dependency: an IntersectionObserver toggles an `.aos-animate`
 * class as elements enter the viewport, and CSS transitions (see
 * utilities/_aos.css) carry the motion. IntersectionObserver is rock-solid
 * across browsers and mobile — no scroll-timeline quirks, nothing freezes
 * mid-animation at the page bottom.
 *
 * Markup:
 *   <div data-aos="fade-up">
 *   <div data-aos="fade-up" data-aos-delay="150">
 *   <div data-aos="zoom-in" data-aos-duration="800">
 *   <div data-aos="fade-up" data-aos-once="false">   — replays each entry
 *
 * Auto-stagger direct children:
 *   <ul data-aos-stagger="90">                        — step in ms (default 90)
 *     <li data-aos="fade-up">…</li>
 *     <li data-aos="fade-up">…</li>
 *   </ul>
 */

const DEFAULT_STAGGER_MS = 90;

function applyDelays(elements: HTMLElement[]): void {
  for (const parent of document.querySelectorAll<HTMLElement>(
    '[data-aos-stagger]'
  )) {
    const step = Number(parent.dataset.aosStagger) || DEFAULT_STAGGER_MS;
    const children = parent.querySelectorAll<HTMLElement>(
      ':scope > [data-aos]'
    );

    children.forEach((child, index) => {
      if (child.dataset.aosDelay === undefined) {
        child.style.setProperty('--aos-delay', `${index * step}ms`);
      }
    });
  }

  for (const el of elements) {
    if (el.dataset.aosDelay !== undefined) {
      el.style.setProperty('--aos-delay', `${Number(el.dataset.aosDelay)}ms`);
    }

    if (el.dataset.aosDuration !== undefined) {
      el.style.setProperty(
        '--aos-duration',
        `${Number(el.dataset.aosDuration)}ms`
      );
    }
  }
}

export function initAos(): void {
  const elements = Array.from(
    document.querySelectorAll<HTMLElement>('[data-aos]')
  );

  if (elements.length === 0) {
    return;
  }

  applyDelays(elements);

  // Reduced motion: reveal everything immediately, skip observation.
  if (globalThis.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    for (const el of elements) {
      el.classList.add('aos-animate');
    }

    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        const el = entry.target as HTMLElement;

        if (entry.isIntersecting) {
          el.classList.add('aos-animate');

          if (el.dataset.aosOnce !== 'false') {
            observer.unobserve(el);
          }
        } else if (el.dataset.aosOnce === 'false') {
          el.classList.remove('aos-animate');
        }
      }
    },
    { rootMargin: '0px 0px -8% 0px', threshold: 0.05 }
  );

  for (const el of elements) {
    observer.observe(el);
  }
}
