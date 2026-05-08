/**
 * Navigation utility functions for scroll behavior
 * These use browser APIs directly and don't need Alpine.js
 */

interface ScrollHeaderOptions {
  scrollThreshold: number;
  heroSelector: string;
}

/**
 * Scroll header component
 * Attach to #header — updates appearance based on scroll position and hero presence
 */
export function scrollHeader(
  options: ScrollHeaderOptions = {
    scrollThreshold: 50,
    heroSelector: '.hero',
  }
) {
  return (el: HTMLElement) => {
    const hasHero = Boolean(document.querySelector(options.heroSelector));

    document.body.classList.toggle('has-hero', hasHero);
    document.body.classList.toggle('no-hero', !hasHero);

    const updateScrollState = (): void => {
      const scrolled = window.scrollY > options.scrollThreshold || !hasHero;
      el.classList.toggle('scrolled', scrolled);
    };

    updateScrollState();
    window.addEventListener('scroll', updateScrollState, { passive: true });

    if (hasHero) {
      const hero = document.querySelector<HTMLElement>(options.heroSelector);
      if (!hero) return;

      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            el.classList.toggle(
              'header--on-hero',
              entry.isIntersecting && entry.intersectionRatio > 0.15
            );
          });
        },
        {
          threshold: [0, 0.15, 0.35, 0.5],
          rootMargin: '-10% 0px 0px 0px',
        }
      );

      observer.observe(hero);
    }
  };
}

interface ScrollToTopOptions {
  scrollThreshold: number;
  visibleClass: string;
}

/**
 * Scroll-to-top button component
 * Attach to #scrollToTop — shows/hides based on scroll position
 */
export function scrollToTop(
  options: ScrollToTopOptions = {
    scrollThreshold: 400,
    visibleClass: 'visible',
  }
) {
  return (el: HTMLElement) => {
    const updateVisibility = (): void => {
      el.classList.toggle(
        options.visibleClass,
        window.scrollY > options.scrollThreshold
      );
    };

    updateVisibility();
    window.addEventListener('scroll', updateVisibility, { passive: true });

    el.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  };
}

interface ScrollProgressOptions {
  smoothing: number;
}

/**
 * Scroll progress indicator
 *
 * Drives the top progress bar via inline `transform: scaleX(...)`. We do this
 * in JS instead of `animation-timeline: scroll(root)` because the native
 * timeline remaps to a new percentage the instant `scrollHeight` changes
 * (lazy images settling, fonts loading, accordions opening, Swup container
 * swaps), which the user perceives as the bar "jumping" while scrolling.
 *
 * A small lerp toward the target value smooths those height-change jumps
 * into a glide while still only animating `transform` on the compositor.
 */
export function scrollProgress(
  options: ScrollProgressOptions = {
    smoothing: 0.18,
  }
) {
  return (el: HTMLElement) => {
    const { smoothing } = options;
    const root = document.documentElement;
    let target = 0;
    let current = 0;
    let raf = 0;
    let running = false;

    const computeTarget = (): void => {
      const max = root.scrollHeight - globalThis.innerHeight;
      target = max > 0 ? Math.min(1, Math.max(0, root.scrollTop / max)) : 0;
    };

    const tick = (): void => {
      const delta = target - current;

      if (Math.abs(delta) < 0.0005) {
        current = target;
        el.style.transform = `scaleX(${current})`;
        running = false;
        return;
      }

      current += delta * smoothing;
      el.style.transform = `scaleX(${current})`;
      raf = requestAnimationFrame(tick);
    };

    const schedule = (): void => {
      computeTarget();
      if (running) return;
      running = true;
      raf = requestAnimationFrame(tick);
    };

    computeTarget();
    current = target;
    el.style.transform = `scaleX(${current})`;

    globalThis.addEventListener('scroll', schedule, { passive: true });
    globalThis.addEventListener('resize', schedule);

    const resizeObserver =
      typeof ResizeObserver === 'function' ? new ResizeObserver(schedule) : null;

    resizeObserver?.observe(root);
    if (document.body) resizeObserver?.observe(document.body);
  };
}
