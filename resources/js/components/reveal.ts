/**
 * Scroll reveal — a single, deliberate motion for content arriving in
 * the reading area. Content rises and settles; that is all.
 *
 * Timing and distance are chosen so the motion is visible without
 * drawing attention to itself. A deep expo ease-out lands softly.
 *
 * Markers:
 *  - [data-reveal]           single element
 *  - [data-reveal-stagger]   direct children cascade
 *  - [data-reveal-delay="N"] optional 1..4 extra 100ms steps (single elements)
 *
 * CSS hides these markers before paint while <html> has the `.js`
 * class. When the animation ends we set `data-revealed` and clear all
 * inline styles — no lingering transform, no stacking context.
 */

import { animate } from 'motion/mini';
import { defineComponent } from '@beardcoder/stitch-js';

const DISTANCE_PX = 24;
const DURATION_S = 0.9;
const STAGGER_S = 0.1;
const EASE: [number, number, number, number] = [0.16, 1, 0.3, 1];
const ROOT_MARGIN = '0px 0px -12% 0px';

function reducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function settle(el: HTMLElement): void {
  el.dataset.revealed = '';
  el.style.willChange = '';
  el.style.opacity = '';
  el.style.transform = '';
}

function play(el: HTMLElement, delay = 0): Promise<void> {
  return animate(
    el,
    {
      opacity: [0, 1],
      transform: [
        `translate3d(0, ${DISTANCE_PX}px, 0)`,
        'translate3d(0, 0, 0)',
      ],
    },
    { duration: DURATION_S, delay, ease: EASE }
  ).then(() => settle(el));
}

function observeOnce(target: Element, onEnter: () => void): () => void {
  const io = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (!entry.isIntersecting) continue;
        onEnter();
        io.disconnect();

        return;
      }
    },
    { threshold: 0, rootMargin: ROOT_MARGIN }
  );

  io.observe(target);

  return () => io.disconnect();
}

function readDelay(el: HTMLElement): number {
  const raw = el.dataset.revealDelay;

  if (!raw) return 0;

  const n = Number.parseInt(raw, 10);

  return Number.isFinite(n) ? Math.max(0, Math.min(n, 4)) * STAGGER_S : 0;
}

/** Single-element reveal. Attach to `[data-reveal]`. */
export const reveal = defineComponent({}, (ctx) => {
  const el = ctx.el;

  // Children of a stagger container are animated by their parent.
  if (el.parentElement?.hasAttribute('data-reveal-stagger')) return;

  if (reducedMotion()) {
    settle(el);

    return;
  }

  el.style.willChange = 'opacity, transform';

  const stop = observeOnce(el, () => {
    void play(el, readDelay(el));
  });

  ctx.onDestroy(() => stop());
});

/**
 * Stagger reveal — direct children cascade with a 100ms step.
 * Attach to `[data-reveal-stagger]`.
 */
export const revealStagger = defineComponent({}, (ctx) => {
  const children = Array.from(ctx.el.children).filter(
    (node): node is HTMLElement => node instanceof HTMLElement
  );

  if (children.length === 0) return;

  if (reducedMotion()) {
    children.forEach(settle);

    return;
  }

  children.forEach((child) => {
    child.style.willChange = 'opacity, transform';
  });

  const stop = observeOnce(ctx.el, () => {
    children.forEach((child, i) => {
      void play(child, i * STAGGER_S);
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
 * brightens as that step comes into view. Feedback, not decoration.
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

    const reduced = reducedMotion();
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

      const io = new IntersectionObserver(
        (entries) => {
          for (const entry of entries) {
            if (!entry.isIntersecting) continue;
            step.classList.add(ctx.options.activeClass);

            if (!reduced && number) {
              animate(
                number,
                { opacity: 0.25, transform: 'scale(1)' },
                { duration: 0.8, ease: EASE }
              ).then(() => {
                number.style.willChange = '';
              });
            }

            io.disconnect();

            return;
          }
        },
        { threshold: 0.3, rootMargin: '0px 0px -20% 0px' }
      );

      io.observe(step);
      stoppers.push(() => io.disconnect());
    });

    ctx.onDestroy(() => stoppers.forEach((s) => s()));
  }
);

interface ActiveSectionOptions {
  sectionIds: string[];
  linkSelector: string;
  activeClass: string;
}

/** Highlight the nav link whose section is currently in the reading area. */
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
