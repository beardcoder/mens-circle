/**
 * Scroll-reveal and journey-progress motion layer.
 * Built on Motion.dev's WAAPI-backed `motion/mini` — one calm, unified
 * reveal for the whole site.
 *
 * The previous direction-variant reveals (up/down/left/right/scale) felt
 * restless; everything here settles in the same direction with the same
 * curve so the page arrives instead of performing.
 *
 * `motion/mini` only ships `animate`; viewport detection uses a plain
 * IntersectionObserver and stagger delays are computed inline — so we
 * stay on the small WAAPI runtime without pulling in the full engine.
 */

import { animate } from 'motion/mini';
import { defineComponent } from '@beardcoder/stitch-js';

const REVEAL_DISTANCE_PX = 10;
const REVEAL_DURATION_S = 0.7;
const STAGGER_STEP_S = 0.08;
const EASE_SETTLE: [number, number, number, number] = [0.22, 1, 0.36, 1];
const IN_VIEW_AMOUNT = 0.12;

function prefersReducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function hideForReveal(el: HTMLElement): void {
  el.style.opacity = '0';
  el.style.transform = `translate3d(0, ${REVEAL_DISTANCE_PX}px, 0)`;
  el.style.willChange = 'opacity, transform';
}

function settleStyles(el: HTMLElement): void {
  el.style.opacity = '';
  el.style.transform = '';
  el.style.willChange = '';
}

function readDelayClass(el: HTMLElement): number {
  for (let i = 1; i <= 4; i++) {
    if (
      el.classList.contains(`fade-in-delay-${i}`) ||
      el.classList.contains(`delay-${i}`)
    ) {
      return i * STAGGER_STEP_S;
    }
  }

  return 0;
}

/**
 * Observe `target` once; run `onEnter` the first time it crosses
 * `amount` visibility, then stop. Returns a disposer.
 */
function onceInView(
  target: Element,
  onEnter: () => void,
  options: { amount?: number; rootMargin?: string } = {}
): () => void {
  const observer = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (!entry.isIntersecting) continue;
        onEnter();
        observer.disconnect();

        return;
      }
    },
    {
      threshold: options.amount ?? IN_VIEW_AMOUNT,
      rootMargin: options.rootMargin ?? '0px',
    }
  );

  observer.observe(target);

  return () => observer.disconnect();
}

/**
 * Reveal a single element when it enters the viewport.
 * Attach to `.fade-in`, `.fade-in-up`, `.fade-in-down`, `.fade-in-left`,
 * `.fade-in-right`, `.fade-in-scale` — all variants now share the same motion.
 */
export const reveal = defineComponent({}, (ctx) => {
  const el = ctx.el;

  // Children of a stagger container are handled by revealStagger.
  if (el.closest('.stagger-children, [data-reveal-stagger]')) return;

  if (prefersReducedMotion()) return;

  const delay = readDelayClass(el);

  hideForReveal(el);

  const stop = onceInView(el, () => {
    animate(
      el,
      { opacity: 1, transform: 'translate3d(0, 0, 0)' },
      { duration: REVEAL_DURATION_S, delay, ease: EASE_SETTLE }
    ).then(() => settleStyles(el));
  });

  ctx.onDestroy(() => stop());
});

/**
 * Reveal the direct children of a container with a gentle cascade.
 * Attach to `.stagger-children` or `[data-reveal-stagger]`.
 */
export const revealStagger = defineComponent({}, (ctx) => {
  const children = Array.from(ctx.el.children).filter(
    (node): node is HTMLElement => node instanceof HTMLElement
  );

  if (children.length === 0) return;
  if (prefersReducedMotion()) return;

  children.forEach(hideForReveal);

  const stop = onceInView(ctx.el, () => {
    children.forEach((child, index) => {
      animate(
        child,
        { opacity: 1, transform: 'translate3d(0, 0, 0)' },
        {
          duration: REVEAL_DURATION_S,
          delay: index * STAGGER_STEP_S,
          ease: EASE_SETTLE,
        }
      ).then(() => settleStyles(child));
    });
  });

  ctx.onDestroy(() => stop());
});

interface JourneyProgressOptions {
  stepSelector: string;
  numberSelector: string;
  activeClass: string;
}

/**
 * Journey step orientation — the big number behind each step quietly
 * brightens and settles when that step comes into view. This is feedback,
 * not decoration: it tells the reader "you are here".
 */
export const journeyProgress = defineComponent<JourneyProgressOptions>(
  {
    stepSelector: '.journey__step',
    numberSelector: '.journey__step-number',
    activeClass: 'journey__step--active',
  },
  (ctx) => {
    const steps = ctx.el.querySelectorAll<HTMLElement>(
      ctx.options.stepSelector
    );

    if (steps.length === 0) return;

    const reduced = prefersReducedMotion();
    const stoppers: Array<() => void> = [];

    steps.forEach((step) => {
      const number = step.querySelector<HTMLElement>(
        ctx.options.numberSelector
      );

      if (!reduced && number) {
        number.style.opacity = '0.08';
        number.style.transform = 'scale(0.97)';
        number.style.willChange = 'opacity, transform';
      }

      const stop = onceInView(
        step,
        () => {
          step.classList.add(ctx.options.activeClass);

          if (reduced || !number) return;

          animate(
            number,
            { opacity: 0.25, transform: 'scale(1)' },
            { duration: 0.8, ease: EASE_SETTLE }
          ).then(() => {
            number.style.willChange = '';
          });
        },
        { amount: 0.3, rootMargin: '0px 0px -20% 0px' }
      );

      stoppers.push(stop);
    });

    ctx.onDestroy(() => stoppers.forEach((stop) => stop()));
  }
);

interface ActiveSectionOptions {
  sectionIds: string[];
  linkSelector: string;
  activeClass: string;
}

/**
 * Highlights the nav link whose section is currently in the reading area.
 * Pure orientation — no motion, just a class toggle.
 */
export const activeSection = defineComponent<ActiveSectionOptions>(
  {
    sectionIds: ['ueber', 'reise', 'faq', 'newsletter'],
    linkSelector: '.nav__link[href*="#"]',
    activeClass: 'nav__link--active',
  },
  (ctx) => {
    const sections = ctx.options.sectionIds
      .map((id) => document.getElementById(id))
      .filter((el): el is HTMLElement => el !== null);

    if (sections.length === 0) return;

    const navLinks = ctx.el.querySelectorAll<HTMLAnchorElement>(
      ctx.options.linkSelector
    );

    if (navLinks.length === 0) return;

    const setActive = (sectionId: string | null): void => {
      navLinks.forEach((link) => {
        const href = link.getAttribute('href') ?? '';
        const isActive = sectionId !== null && href.endsWith(`#${sectionId}`);

        link.classList.toggle(ctx.options.activeClass, isActive);
      });
    };

    const observer = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (entry.isIntersecting) {
            setActive(entry.target.id);
          }
        }
      },
      { threshold: 0.3, rootMargin: '-20% 0px -40% 0px' }
    );

    sections.forEach((section) => observer.observe(section));
    ctx.onDestroy(() => observer.disconnect());
  }
);
