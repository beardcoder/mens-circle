/**
 * Animation Components using Motion.dev
 *
 * Optimized for natural, smooth animations with spring-like easing.
 * Uses Motion's inView function for performant scroll-triggered animations.
 *
 * @see https://motion.dev/docs/inview
 */

import { animate, inView } from 'motion';

/**
 * Animation configuration for different animation types
 * Using smaller translate values for subtler, more elegant motion
 */
interface AnimationConfig {
  initial: {
    opacity: number;
    y?: number;
    x?: number;
    scale?: number;
  };
  animate: {
    opacity: number;
    y?: number;
    x?: number;
    scale?: number;
  };
}

const ANIMATION_CONFIGS: Record<string, AnimationConfig> = {
  'fade-in': {
    initial: { opacity: 0, y: 20 },
    animate: { opacity: 1, y: 0 },
  },
  'fade-in-up': {
    initial: { opacity: 0, y: 20 },
    animate: { opacity: 1, y: 0 },
  },
  'fade-in-down': {
    initial: { opacity: 0, y: -20 },
    animate: { opacity: 1, y: 0 },
  },
  'fade-in-left': {
    initial: { opacity: 0, x: -20 },
    animate: { opacity: 1, x: 0 },
  },
  'fade-in-right': {
    initial: { opacity: 0, x: 20 },
    animate: { opacity: 1, x: 0 },
  },
  'fade-in-scale': {
    initial: { opacity: 0, scale: 0.95 },
    animate: { opacity: 1, scale: 1 },
  },
};

/**
 * Easing curves optimized for natural, organic motion
 *
 * These bezier curves simulate spring-like physics:
 * - Quick initial movement
 * - Smooth deceleration with slight overshoot feel
 */
const EASING = {
  // Spring-like easing - quick start, smooth settle (slight overshoot feel)
  spring: [0.22, 1.2, 0.36, 1] as [number, number, number, number],
  // Smooth ease-out - natural deceleration
  smooth: [0.25, 1, 0.5, 1] as [number, number, number, number],
  // Natural deceleration for opacity
  decelerate: [0, 0, 0.2, 1] as [number, number, number, number],
  // Gentle spring - more bounce
  gentle: [0.34, 1.56, 0.64, 1] as [number, number, number, number],
};

/**
 * Timing configuration - optimized for smooth, natural motion
 */
const TIMING = {
  // Duration for transform animations (slightly faster for snappier feel)
  transformDuration: 0.6,
  // Duration for opacity (faster than transform for instant visibility)
  opacityDuration: 0.4,
  // Stagger delay between children (reduced for faster sequence)
  staggerDelay: 0.06,
  // Viewport trigger amount (increased for earlier triggers)
  viewportAmount: 0.1,
};

/**
 * Delay values for staggered animations (in seconds)
 */
const DELAY_VALUES: Record<string, number> = {
  'fade-in-delay-1': 0.1,
  'fade-in-delay-2': 0.18,
  'fade-in-delay-3': 0.26,
  'fade-in-delay-4': 0.34,
  'fade-in-delay-5': 0.42,
  'delay-1': 0.1,
  'delay-2': 0.18,
  'delay-3': 0.26,
  'delay-4': 0.34,
  'delay-5': 0.42,
};

/**
 * Check if user prefers reduced motion
 */
function prefersReducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * Get animation type from element's class list
 */
function getAnimationType(element: Element): string | null {
  const animationClasses = Object.keys(ANIMATION_CONFIGS);

  for (const cls of animationClasses) {
    if (element.classList.contains(cls)) {
      return cls;
    }
  }

  return null;
}

/**
 * Get delay from element's class list
 */
function getDelay(element: Element): number {
  for (const [cls, delay] of Object.entries(DELAY_VALUES)) {
    if (element.classList.contains(cls)) {
      return delay;
    }
  }

  return 0;
}

/**
 * Build transform string from config
 */
function buildTransform(
  config: AnimationConfig['initial' | 'animate']
): string {
  const transforms: string[] = [];

  if (config.y !== undefined) {
    transforms.push(`translateY(${config.y}px)`);
  }

  if (config.x !== undefined) {
    transforms.push(`translateX(${config.x}px)`);
  }

  if (config.scale !== undefined) {
    transforms.push(`scale(${config.scale})`);
  }

  return transforms.length > 0 ? transforms.join(' ') : 'none';
}

/**
 * Initialize scroll-triggered animations using Motion's inView
 */
export function initScrollAnimations(): void {
  // Skip if user prefers reduced motion
  if (prefersReducedMotion()) {
    showAllElementsImmediately();

    return;
  }

  // Initialize individual fade-in animations
  initFadeAnimations();

  // Initialize staggered children animations
  initStaggerAnimations();
}

/**
 * Show all animated elements immediately (for reduced motion)
 */
function showAllElementsImmediately(): void {
  const animatedElements = document.querySelectorAll(
    '.fade-in, .fade-in-up, .fade-in-down, .fade-in-left, .fade-in-right, .fade-in-scale'
  );

  animatedElements.forEach((el) => {
    (el as HTMLElement).style.opacity = '1';
    (el as HTMLElement).style.transform = 'none';
  });

  const staggerContainers = document.querySelectorAll('.stagger-children');

  staggerContainers.forEach((container) => {
    const children = container.children;

    for (let i = 0; i < children.length; i++) {
      (children[i] as HTMLElement).style.opacity = '1';
      (children[i] as HTMLElement).style.transform = 'none';
    }
  });
}

/**
 * Initialize fade-in animations for individual elements
 */
function initFadeAnimations(): void {
  const selectors = Object.keys(ANIMATION_CONFIGS)
    .map((cls) => `.${cls}:not(.stagger-children > *)`)
    .join(', ');

  const elements = document.querySelectorAll<HTMLElement>(selectors);

  elements.forEach((element) => {
    const animationType = getAnimationType(element);

    if (!animationType) return;

    const config = ANIMATION_CONFIGS[animationType];
    const delay = getDelay(element);

    // Set initial state
    element.style.opacity = String(config.initial.opacity);
    element.style.transform = buildTransform(config.initial);

    // Trigger animation when element enters viewport
    inView(
      element,
      () => {
        // Animate opacity with smooth deceleration (faster)
        animate(
          element,
          { opacity: config.animate.opacity },
          {
            duration: TIMING.opacityDuration,
            delay,
            ease: EASING.decelerate,
          }
        );

        // Animate transform with spring-like easing (natural motion)
        animate(
          element,
          { transform: buildTransform(config.animate) },
          {
            duration: TIMING.transformDuration,
            delay,
            ease: EASING.spring,
          }
        );
      },
      {
        amount: TIMING.viewportAmount,
      }
    );
  });
}

/**
 * Initialize staggered animations for container children
 */
function initStaggerAnimations(): void {
  const containers =
    document.querySelectorAll<HTMLElement>('.stagger-children');

  containers.forEach((container) => {
    const children = Array.from(container.children) as HTMLElement[];

    if (children.length === 0) return;

    // Set initial state for all children
    children.forEach((child) => {
      child.style.opacity = '0';
      child.style.transform = 'translateY(20px)';
    });

    // Trigger staggered animation when container enters viewport
    inView(
      container,
      () => {
        // Animate each child with staggered delay
        children.forEach((child, index) => {
          const delay = index * TIMING.staggerDelay;

          // Animate opacity with smooth deceleration
          animate(
            child,
            { opacity: 1 },
            {
              duration: TIMING.opacityDuration,
              delay,
              ease: EASING.decelerate,
            }
          );

          // Animate transform with gentle spring-like easing
          animate(
            child,
            { transform: 'translateY(0px)' },
            {
              duration: TIMING.transformDuration,
              delay,
              ease: EASING.gentle,
            }
          );
        });
      },
      {
        amount: TIMING.viewportAmount,
      }
    );
  });
}

/**
 * Smooth Scroll Component
 * Handles smooth scrolling for anchor links
 */
export function initSmoothScroll(): void {
  document
    .querySelectorAll<HTMLAnchorElement>('a[href^="#"]')
    .forEach((anchor) => {
      anchor.addEventListener('click', function (e: Event) {
        const targetId = this.getAttribute('href');

        // Skip if empty anchor or not a valid selector
        if (
          !targetId ||
          targetId === '#' ||
          !targetId.startsWith('#') ||
          targetId.includes(':')
        ) {
          return;
        }

        try {
          const target = document.querySelector<HTMLElement>(targetId);

          if (target) {
            e.preventDefault();
            const headerHeight =
              document.getElementById('header')?.offsetHeight ?? 0;
            const targetPosition =
              target.getBoundingClientRect().top +
              window.pageYOffset -
              headerHeight -
              20;

            window.scrollTo({
              top: targetPosition,
              behavior: 'smooth',
            });
          }
        } catch {
          // Invalid selector - ignore silently
        }
      });
    });
}
