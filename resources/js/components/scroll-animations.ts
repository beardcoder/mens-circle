/**
 * Scroll Animation Components
 * Smooth, organic scroll-driven animations using stitch-js + IntersectionObserver + Web Animations API
 *
 * Design principles:
 * - Subtle movement distances (12–16px) so elements emerge rather than fly in
 * - Multi-keyframe sequences where opacity leads transform for a natural reveal
 * - Generous stagger delays (120ms) for a gentle cascade effect
 * - Soft deceleration easing that mimics organic motion
 */

import { defineComponent } from '@beardcoder/stitch-js';

/** Base duration for individual element reveals */
const ANIMATION_DURATION = 900;

/** Time between staggered children – enough to perceive each one individually */
const ANIMATION_STAGGER_DELAY = 120;

/** Smooth deceleration curve – fast entry that settles gently */
const ANIMATION_EASING = 'cubic-bezier(0.22, 1, 0.36, 1)';

type Direction = 'up' | 'down' | 'left' | 'right' | 'scale' | 'default';

/**
 * Multi-keyframe animation sequence for a given direction.
 * The middle keyframe (40%) lets opacity arrive before transform finishes,
 * creating a soft, layered reveal instead of a mechanical slide.
 */
function getKeyframesForDirection(direction: Direction): Keyframe[] {
  const distances: Record<Direction, string> = {
    default: 'translateY(14px)',
    up: 'translateY(14px)',
    down: 'translateY(-14px)',
    left: 'translateX(-16px)',
    right: 'translateX(16px)',
    scale: 'scale(0.97)',
  };

  const midTransforms: Record<Direction, string> = {
    default: 'translateY(4px)',
    up: 'translateY(4px)',
    down: 'translateY(-4px)',
    left: 'translateX(-4px)',
    right: 'translateX(4px)',
    scale: 'scale(0.995)',
  };

  return [
    { opacity: 0, transform: distances[direction], offset: 0 },
    { opacity: 1, transform: midTransforms[direction], offset: 0.45 },
    { opacity: 1, transform: 'none', offset: 1 },
  ];
}

function getDirection(element: HTMLElement): Direction {
  if (element.classList.contains('fade-in-up')) return 'up';
  if (element.classList.contains('fade-in-down')) return 'down';
  if (element.classList.contains('fade-in-left')) return 'left';
  if (element.classList.contains('fade-in-right')) return 'right';
  if (element.classList.contains('fade-in-scale')) return 'scale';

  return 'default';
}

function getDelay(element: HTMLElement): number {
  for (let i = 1; i <= 5; i++) {
    if (
      element.classList.contains(`fade-in-delay-${i}`) ||
      element.classList.contains(`delay-${i}`)
    ) {
      return i * ANIMATION_STAGGER_DELAY;
    }
  }

  return 0;
}

/**
 * Cleans up after an animation finishes: removes `will-change` hint
 * and clears inline styles so the element settles into its natural state.
 */
function cleanupAfterAnimation(element: HTMLElement): void {
  element.style.willChange = 'auto';
  element.style.opacity = '';
  element.style.transform = '';
}

interface ScrollAnimateOptions {
  threshold: number;
  rootMargin: string;
}

/**
 * Scroll fade-in animation component
 * Attach to .fade-in, .fade-in-up, .fade-in-down, .fade-in-left, .fade-in-right, .fade-in-scale
 * Triggers a smooth, multi-keyframe reveal on scroll intersection
 */
export const scrollAnimate = defineComponent<ScrollAnimateOptions>(
  {
    threshold: 0.08,
    rootMargin: '0px 0px -5% 0px',
  },
  (ctx) => {
    const prefersReducedMotion = window.matchMedia(
      '(prefers-reduced-motion: reduce)'
    ).matches;

    if (prefersReducedMotion) return;

    // Skip if inside a stagger container (handled by staggerAnimate)
    if (ctx.el.closest('.stagger-children')) return;

    ctx.el.style.opacity = '0';

    const { options: o } = ctx;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;

          const element = entry.target as HTMLElement;
          const delay = getDelay(element);
          const keyframes = getKeyframesForDirection(getDirection(element));

          element.style.willChange = 'opacity, transform';

          const animation = element.animate(keyframes, {
            duration: ANIMATION_DURATION,
            delay,
            easing: ANIMATION_EASING,
            fill: 'forwards',
          });

          animation.onfinish = () => cleanupAfterAnimation(element);

          observer.unobserve(element);
        });
      },
      { threshold: o.threshold, rootMargin: o.rootMargin }
    );

    observer.observe(ctx.el);
    ctx.onDestroy(() => observer.disconnect());
  }
);

interface StaggerAnimateOptions {
  threshold: number;
  rootMargin: string;
}

/**
 * Stagger animation component
 * Attach to .stagger-children — animates child elements with a gentle cascade.
 * Each child uses a multi-keyframe reveal with increasing delay.
 */
export const staggerAnimate = defineComponent<StaggerAnimateOptions>(
  {
    threshold: 0.08,
    rootMargin: '0px 0px -5% 0px',
  },
  (ctx) => {
    const prefersReducedMotion = window.matchMedia(
      '(prefers-reduced-motion: reduce)'
    ).matches;

    if (prefersReducedMotion) return;

    const children = ctx.el.children;

    for (let i = 0; i < children.length; i++) {
      const child = children[i] as HTMLElement;

      child.style.opacity = '0';
    }

    const { options: o } = ctx;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;

          const containerChildren = entry.target.children;

          for (let i = 0; i < containerChildren.length; i++) {
            const child = containerChildren[i] as HTMLElement;

            child.style.willChange = 'opacity, transform';

            const animation = child.animate(
              [
                { opacity: 0, transform: 'translateY(12px)', offset: 0 },
                { opacity: 1, transform: 'translateY(3px)', offset: 0.45 },
                { opacity: 1, transform: 'none', offset: 1 },
              ],
              {
                duration: ANIMATION_DURATION,
                delay: i * ANIMATION_STAGGER_DELAY,
                easing: ANIMATION_EASING,
                fill: 'forwards',
              }
            );

            animation.onfinish = () => cleanupAfterAnimation(child);
          }

          observer.unobserve(entry.target);
        });
      },
      { threshold: o.threshold, rootMargin: o.rootMargin }
    );

    observer.observe(ctx.el);
    ctx.onDestroy(() => observer.disconnect());
  }
);

interface ActiveSectionOptions {
  sectionIds: string[];
  linkSelector: string;
  activeClass: string;
  threshold: number;
  rootMargin: string;
}

/**
 * Active section tracking component
 * Attach to nav element — highlights nav links based on visible section
 */
export const activeSection = defineComponent<ActiveSectionOptions>(
  {
    sectionIds: ['ueber', 'reise', 'faq', 'newsletter'],
    linkSelector: '.nav__link[href*="#"]',
    activeClass: 'nav__link--active',
    threshold: 0.3,
    rootMargin: '-20% 0px -40% 0px',
  },
  (ctx) => {
    const { options: o } = ctx;
    const sections = o.sectionIds
      .map((id) => document.getElementById(id))
      .filter(Boolean) as HTMLElement[];

    if (sections.length === 0) return;

    const navLinks = ctx.el.querySelectorAll<HTMLAnchorElement>(o.linkSelector);

    if (navLinks.length === 0) return;

    const setActive = (sectionId: string | null): void => {
      navLinks.forEach((link) => {
        const href = link.getAttribute('href') ?? '';
        const isActive = sectionId !== null && href.endsWith(`#${sectionId}`);

        link.classList.toggle(o.activeClass, isActive);
      });
    };

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            setActive(entry.target.id);
          }
        });
      },
      { threshold: o.threshold, rootMargin: o.rootMargin }
    );

    sections.forEach((section) => observer.observe(section));
    ctx.onDestroy(() => observer.disconnect());
  }
);

interface JourneyProgressOptions {
  stepSelector: string;
  numberSelector: string;
  activeClass: string;
  threshold: number;
  rootMargin: string;
}

/**
 * Journey progress component
 * Attach to .journey or parent container — animates step numbers on scroll
 */
export const journeyProgress = defineComponent<JourneyProgressOptions>(
  {
    stepSelector: '.journey__step',
    numberSelector: '.journey__step-number',
    activeClass: 'journey__step--active',
    threshold: 0.3,
    rootMargin: '0px 0px -20% 0px',
  },
  (ctx) => {
    const prefersReducedMotion = window.matchMedia(
      '(prefers-reduced-motion: reduce)'
    ).matches;

    if (prefersReducedMotion) return;

    const { options: o } = ctx;
    const steps = ctx.el.querySelectorAll<HTMLElement>(o.stepSelector);

    if (steps.length === 0) return;

    steps.forEach((step) => {
      const number = step.querySelector<HTMLElement>(o.numberSelector);

      if (number) {
        number.style.transition = `opacity 0.8s ${ANIMATION_EASING}, transform 0.8s ${ANIMATION_EASING}`;
        number.style.opacity = '0.08';
        number.style.transform = 'scale(0.97)';
      }
    });

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;

          const number = entry.target.querySelector<HTMLElement>(
            o.numberSelector
          );

          if (number) {
            number.style.opacity = '0.25';
            number.style.transform = 'scale(1)';
          }

          entry.target.classList.add(o.activeClass);
          observer.unobserve(entry.target);
        });
      },
      { threshold: o.threshold, rootMargin: o.rootMargin }
    );

    steps.forEach((step) => observer.observe(step));
    ctx.onDestroy(() => observer.disconnect());
  }
);
