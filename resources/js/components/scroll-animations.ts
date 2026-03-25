/**
 * Scroll Animation Components
 * Smooth scroll-driven animations powered by anime.js + stitch-js
 */

import { defineComponent } from '@stitch';
import { animate, stagger, onScroll, createTimeline } from 'animejs';

type Direction = 'up' | 'down' | 'left' | 'right' | 'scale' | 'default';

function getTransform(direction: Direction): {
  prop: string;
  from: number | string;
} {
  const map: Record<Direction, { prop: string; from: number | string }> = {
    default: { prop: 'translateY', from: '24px' },
    up: { prop: 'translateY', from: '24px' },
    down: { prop: 'translateY', from: '-24px' },
    left: { prop: 'translateX', from: '-32px' },
    right: { prop: 'translateX', from: '32px' },
    scale: { prop: 'scale', from: 0.92 },
  };

  return map[direction];
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
      return i * 100;
    }
  }

  return 0;
}

function prefersReducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

interface ScrollAnimateOptions {
  threshold: number;
  rootMargin: string;
}

/**
 * Scroll fade-in animation component using anime.js
 * Attach to .fade-in, .fade-in-up, .fade-in-down, .fade-in-left, .fade-in-right, .fade-in-scale
 */
export const scrollAnimate = defineComponent<ScrollAnimateOptions>(
  {
    threshold: 0.1,
    rootMargin: '0px 0px -10% 0px',
  },
  (ctx) => {
    if (prefersReducedMotion()) return;

    // Skip if inside a stagger container (handled by staggerAnimate)
    if (ctx.el.closest('.stagger-children')) return;

    const direction = getDirection(ctx.el);
    const { prop, from } = getTransform(direction);
    const delay = getDelay(ctx.el);

    // Set initial hidden state
    ctx.el.style.opacity = '0';

    if (prop === 'scale') {
      ctx.el.style.transform = `scale(${from})`;
    } else {
      ctx.el.style.transform = `${prop}(${from})`;
    }

    const scrollObs = onScroll({
      target: ctx.el,
      enter: 'bottom -= 10%',
      onEnter: () => {
        if (prop === 'scale') {
          animate(ctx.el, {
            opacity: [0, 1],
            scale: [from, 1],
            duration: 800,
            delay,
            ease: 'outQuint',
          });
        } else if (prop === 'translateY') {
          animate(ctx.el, {
            opacity: [0, 1],
            translateY: [from, '0px'],
            duration: 800,
            delay,
            ease: 'outQuint',
          });
        } else {
          animate(ctx.el, {
            opacity: [0, 1],
            translateX: [from, '0px'],
            duration: 800,
            delay,
            ease: 'outQuint',
          });
        }
      },
    });

    ctx.onDestroy(() => scrollObs.revert());
  }
);

interface StaggerAnimateOptions {
  threshold: number;
}

/**
 * Stagger animation component using anime.js
 * Attach to .stagger-children — animates child elements with staggered delay
 */
export const staggerAnimate = defineComponent<StaggerAnimateOptions>(
  {
    threshold: 0.1,
  },
  (ctx) => {
    if (prefersReducedMotion()) return;

    const children = Array.from(ctx.el.children) as HTMLElement[];

    children.forEach((child) => {
      child.style.opacity = '0';
      child.style.transform = 'translateY(28px)';
    });

    const scrollObs = onScroll({
      target: ctx.el,
      enter: 'bottom -= 10%',
      onEnter: () => {
        animate(children, {
          opacity: [0, 1],
          translateY: ['28px', '0px'],
          duration: 700,
          delay: stagger(90, { ease: 'outQuad' }),
          ease: 'outQuint',
        });
      },
    });

    ctx.onDestroy(() => scrollObs.revert());
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
}

/**
 * Journey progress component using anime.js
 * Attach to .journey or parent container — animates step numbers on scroll with spring physics
 */
export const journeyProgress = defineComponent<JourneyProgressOptions>(
  {
    stepSelector: '.journey__step',
    numberSelector: '.journey__step-number',
    activeClass: 'journey__step--active',
  },
  (ctx) => {
    if (prefersReducedMotion()) return;

    const { options: o } = ctx;
    const steps = ctx.el.querySelectorAll<HTMLElement>(o.stepSelector);

    if (steps.length === 0) return;

    const observers: ScrollObserverCleanup[] = [];

    steps.forEach((step) => {
      const number = step.querySelector<HTMLElement>(o.numberSelector);

      if (number) {
        number.style.opacity = '0.08';
        number.style.transform = 'scale(0.92)';
      }

      const scrollObs = onScroll({
        target: step,
        enter: 'bottom -= 20%',
        onEnter: () => {
          step.classList.add(o.activeClass);

          if (number) {
            animate(number, {
              opacity: [0.08, 0.25],
              scale: [0.92, 1],
              duration: 900,
              ease: 'outQuint',
            });
          }
        },
      });

      observers.push(scrollObs);
    });

    ctx.onDestroy(() => {
      observers.forEach((obs) => obs.revert());
    });
  }
);

interface ScrollObserverCleanup {
  revert: () => ScrollObserverCleanup;
}

/**
 * Hero entrance animation using anime.js timeline
 * Attach to .hero — orchestrates a cinematic reveal sequence
 */
export const heroEntrance = defineComponent({}, (ctx) => {
  if (prefersReducedMotion()) return;

  const hero = ctx.el;
  const label = hero.querySelector('.hero__label');
  const title = hero.querySelector('.hero__title');
  const titleLines = hero.querySelectorAll('.hero__title-line');
  const description = hero.querySelector('.hero__description');
  const cta = hero.querySelector('.hero__cta');
  const scrollIndicator = hero.querySelector('.hero__scroll');
  const circles = hero.querySelectorAll('.hero__circle');

  // Set initial states
  const elements = [label, title, description, cta, scrollIndicator].filter(
    Boolean
  ) as HTMLElement[];

  elements.forEach((el) => {
    el.style.opacity = '0';
  });

  circles.forEach((circle) => {
    (circle as HTMLElement).style.opacity = '0';
  });

  const tl = createTimeline({
    defaults: {
      ease: 'outQuint',
    },
  });

  // Circles fade in gently first
  if (circles.length > 0) {
    tl.add(
      circles,
      {
        opacity: [0, 0.15],
        scale: [0.85, 1],
        duration: 1600,
        delay: stagger(200),
        ease: 'outCubic',
      },
      0
    );
  }

  // Label slides in
  if (label) {
    tl.add(
      label,
      {
        opacity: [0, 1],
        translateX: ['-20px', '0px'],
        duration: 700,
        ease: 'outQuint',
      },
      200
    );
  }

  // Title lines reveal with stagger or full title
  if (titleLines.length > 0) {
    tl.add(
      titleLines,
      {
        opacity: [0, 1],
        translateY: ['40px', '0px'],
        duration: 900,
        delay: stagger(120),
        ease: 'outQuint',
      },
      350
    );

    // Ensure parent title is visible
    if (title) {
      (title as HTMLElement).style.opacity = '1';
    }
  } else if (title) {
    tl.add(
      title,
      {
        opacity: [0, 1],
        translateY: ['40px', '0px'],
        duration: 900,
        ease: 'outQuint',
      },
      350
    );
  }

  // Description fades in
  if (description) {
    tl.add(
      description,
      {
        opacity: [0, 1],
        translateY: ['20px', '0px'],
        duration: 700,
        ease: 'outQuint',
      },
      700
    );
  }

  // CTA button appears with a subtle bounce
  if (cta) {
    tl.add(
      cta,
      {
        opacity: [0, 1],
        translateY: ['16px', '0px'],
        scale: [0.95, 1],
        duration: 600,
        ease: 'outBack(1.4)',
      },
      900
    );
  }

  // Scroll indicator appears last
  if (scrollIndicator) {
    tl.add(
      scrollIndicator,
      {
        opacity: [0, 1],
        translateY: ['10px', '0px'],
        duration: 500,
        ease: 'outCubic',
      },
      1100
    );
  }

  ctx.onDestroy(() => tl.revert());
});

/**
 * Decorative breathing circles animation using anime.js
 * Attach to containers with .hero__circles, .event-register__circles, etc.
 * Replaces CSS @keyframes breathe/breathe-slow with smoother anime.js looping
 */
export const breathingCircles = defineComponent({}, (ctx) => {
  if (prefersReducedMotion()) return;

  const circles = ctx.el.querySelectorAll<HTMLElement>('[class*="circle--"]');

  if (circles.length === 0) return;

  const animations: Array<{ revert: () => void }> = [];

  circles.forEach((circle, index) => {
    // Remove any CSS animation that might be applied
    circle.style.animation = 'none';

    const isSlowVariant = index % 2 === 1;
    const baseDuration = isSlowVariant ? 8000 : 6000;
    const durationOffset = index * 2000;

    // Breathing: opacity + scale pulse — higher opacity range for better contrast
    const breatheAnim = animate(circle, {
      opacity: isSlowVariant ? [0.12, 0.4, 0.12] : [0.18, 0.5, 0.18],
      scale: isSlowVariant ? [0.92, 1.08, 0.92] : [0.9, 1.15, 0.9],
      rotate: isSlowVariant ? ['0deg', '1.5deg', '-0.5deg', '0deg'] : '0deg',
      duration: baseDuration + durationOffset,
      loop: true,
      ease: 'inOutSine',
      delay: index * 800,
    });

    animations.push(breatheAnim);

    // Subtle drift movement for organic feel
    const driftAnim = animate(circle, {
      translateX: ['0px', `${6 + index * 3}px`, `${-4 + index * 2}px`, '0px'],
      translateY: ['0px', `${-10 - index * 2}px`, `${5 + index}px`, '0px'],
      duration: (baseDuration + durationOffset) * 1.7,
      loop: true,
      ease: 'inOutSine',
      delay: index * 500,
      composition: 'blend',
    });

    animations.push(driftAnim);
  });

  ctx.onDestroy(() => {
    animations.forEach((anim) => anim.revert());
  });
});

/**
 * Scroll-pulse animation for the hero scroll indicator line
 * Replaces CSS @keyframes scroll-pulse with a smoother anime.js loop
 */
export const scrollPulse = defineComponent({}, (ctx) => {
  if (prefersReducedMotion()) return;

  const anim = animate(ctx.el, {
    opacity: [1, 0.2, 1],
    scaleY: [1, 0.3, 1],
    duration: 2800,
    loop: true,
    ease: 'inOutSine',
  });

  ctx.onDestroy(() => anim.revert());
});

/**
 * Animated gradient background glow
 * Creates a slowly shifting radial gradient glow effect on dark backgrounds.
 * Attach to .hero__bg, .journey-section, .event-register-section, etc.
 */
export const animatedGradient = defineComponent({}, (ctx) => {
  if (prefersReducedMotion()) return;

  const el = ctx.el;

  // Create a gradient overlay element
  const overlay = document.createElement('div');

  overlay.setAttribute('aria-hidden', 'true');
  overlay.style.cssText = `
      position: absolute;
      inset: 0;
      pointer-events: none;
      z-index: 0;
      opacity: 0;
      background: radial-gradient(
        ellipse 80% 60% at var(--gx, 50%) var(--gy, 30%),
        color-mix(in oklch, var(--accent-primary) 20%, transparent) 0%,
        color-mix(in oklch, var(--color-terracotta-light) 6%, transparent) 40%,
        transparent 70%
      );
    `;

  // Ensure parent has position context
  const computed = getComputedStyle(el);

  if (computed.position === 'static') {
    el.style.position = 'relative';
  }

  el.appendChild(overlay);

  // Fade in the overlay
  const fadeIn = animate(overlay, {
    opacity: [0, 1],
    duration: 1500,
    ease: 'outCubic',
  });

  // Animate the gradient position using a JS object proxy
  const gradientState = { gx: 50, gy: 30 };

  const gradientAnim = animate(gradientState, {
    gx: [30, 70, 40, 60, 30],
    gy: [20, 50, 30, 60, 20],
    duration: 20000,
    loop: true,
    ease: 'inOutSine',
    onRender: () => {
      overlay.style.setProperty('--gx', `${gradientState.gx}%`);
      overlay.style.setProperty('--gy', `${gradientState.gy}%`);
    },
  });

  ctx.onDestroy(() => {
    fadeIn.revert();
    gradientAnim.revert();
    overlay.remove();
  });
});
