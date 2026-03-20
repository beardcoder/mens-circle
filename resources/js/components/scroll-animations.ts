/**
 * Scroll Animation Components
 * Lightweight scroll-driven animations using stitch-js + IntersectionObserver + Web Animations API
 */

import { defineComponent } from '@stitch';

const ANIMATION_DURATION = 700;
const ANIMATION_STAGGER_DELAY = 80;
const ANIMATION_EASING = 'cubic-bezier(0.16, 1, 0.3, 1)';

type Direction = 'up' | 'down' | 'left' | 'right' | 'scale' | 'default';

interface AnimationTransform {
  from: { opacity: number; transform: string };
  to: { opacity: number; transform: string };
}

function getTransformForDirection(direction: Direction): AnimationTransform {
  const transforms: Record<Direction, string> = {
    default: 'translateY(24px)',
    up: 'translateY(24px)',
    down: 'translateY(-24px)',
    left: 'translateX(-24px)',
    right: 'translateX(24px)',
    scale: 'scale(0.95)',
  };

  return {
    from: { opacity: 0, transform: transforms[direction] },
    to: { opacity: 1, transform: 'translateY(0) translateX(0) scale(1)' },
  };
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

interface ScrollAnimateOptions {
  threshold: number;
  rootMargin: string;
}

/**
 * Scroll fade-in animation component
 * Attach to .fade-in, .fade-in-up, .fade-in-down, .fade-in-left, .fade-in-right, .fade-in-scale
 * Triggers Web Animations API fade-in on scroll intersection
 */
export const scrollAnimate = defineComponent<ScrollAnimateOptions>(
  {
    threshold: 0.1,
    rootMargin: '0px 0px -10% 0px',
  },
  (ctx) => {
    const prefersReducedMotion = window.matchMedia(
      '(prefers-reduced-motion: reduce)'
    ).matches;

    if (prefersReducedMotion) return;

    // Skip if inside a stagger container (handled by staggerAnimate)
    if (ctx.el.closest('.stagger-children')) return;

    const direction = getDirection(ctx.el);
    const { from } = getTransformForDirection(direction);

    ctx.el.style.opacity = String(from.opacity);
    ctx.el.style.transform = from.transform;

    const { options: o } = ctx;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;

          const element = entry.target as HTMLElement;
          const delay = getDelay(element);
          const dir = getDirection(element);
          const transforms = getTransformForDirection(dir);

          element.style.willChange = 'opacity, transform';

          const animation = element.animate(
            [
              {
                opacity: transforms.from.opacity,
                transform: transforms.from.transform,
              },
              {
                opacity: transforms.to.opacity,
                transform: transforms.to.transform,
              },
            ],
            {
              duration: ANIMATION_DURATION,
              delay,
              easing: ANIMATION_EASING,
              fill: 'forwards',
            }
          );

          animation.onfinish = () => {
            element.style.willChange = 'auto';
          };

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
 * Attach to .stagger-children — animates child elements with staggered delay
 */
export const staggerAnimate = defineComponent<StaggerAnimateOptions>(
  {
    threshold: 0.1,
    rootMargin: '0px 0px -10% 0px',
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
      child.style.transform = 'translateY(20px)';
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
                { opacity: 0, transform: 'translateY(20px)' },
                { opacity: 1, transform: 'translateY(0)' },
              ],
              {
                duration: ANIMATION_DURATION,
                delay: i * ANIMATION_STAGGER_DELAY,
                easing: ANIMATION_EASING,
                fill: 'forwards',
              }
            );

            animation.onfinish = () => {
              child.style.willChange = 'auto';
            };
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
        number.style.transition = `opacity 0.6s ${ANIMATION_EASING}, transform 0.6s ${ANIMATION_EASING}`;
        number.style.opacity = '0.08';
        number.style.transform = 'scale(0.95)';
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
