/**
 * Animation Components using Motion.dev
 *
 * Uses Motion's inView function for performant scroll-triggered animations.
 * @see https://motion.dev/docs/inview
 */

import { animate, inView } from 'motion';

/**
 * Animation configuration for different animation types
 */
interface AnimationConfig {
  initial: {
    opacity: number;
    transform: string;
  };
  animate: {
    opacity: number;
    transform: string;
  };
}

const ANIMATION_CONFIGS: Record<string, AnimationConfig> = {
  'fade-in': {
    initial: { opacity: 0, transform: 'translateY(30px)' },
    animate: { opacity: 1, transform: 'translateY(0px)' },
  },
  'fade-in-up': {
    initial: { opacity: 0, transform: 'translateY(30px)' },
    animate: { opacity: 1, transform: 'translateY(0px)' },
  },
  'fade-in-down': {
    initial: { opacity: 0, transform: 'translateY(-30px)' },
    animate: { opacity: 1, transform: 'translateY(0px)' },
  },
  'fade-in-left': {
    initial: { opacity: 0, transform: 'translateX(-30px)' },
    animate: { opacity: 1, transform: 'translateX(0px)' },
  },
  'fade-in-right': {
    initial: { opacity: 0, transform: 'translateX(30px)' },
    animate: { opacity: 1, transform: 'translateX(0px)' },
  },
  'fade-in-scale': {
    initial: { opacity: 0, transform: 'scale(0.95)' },
    animate: { opacity: 1, transform: 'scale(1)' },
  },
};

/**
 * Custom ease-out easing curve as a tuple
 */
const EASE_OUT: [number, number, number, number] = [0.16, 1, 0.3, 1];

/**
 * Default animation duration in seconds
 */
const DURATION = 0.6;

/**
 * Delay values for staggered animations (in seconds)
 */
const DELAY_VALUES: Record<string, number> = {
  'fade-in-delay-1': 0.1,
  'fade-in-delay-2': 0.2,
  'fade-in-delay-3': 0.3,
  'fade-in-delay-4': 0.4,
  'fade-in-delay-5': 0.5,
  'delay-1': 0.1,
  'delay-2': 0.2,
  'delay-3': 0.3,
  'delay-4': 0.4,
  'delay-5': 0.5,
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
 * Initialize scroll-triggered animations using Motion's inView
 */
export function initScrollAnimations(): void {
  // Skip if user prefers reduced motion
  if (prefersReducedMotion()) {
    // Show all elements immediately without animation
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

    return;
  }

  // Initialize individual fade-in animations
  initFadeAnimations();

  // Initialize staggered children animations
  initStaggerAnimations();
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
    element.style.transform = config.initial.transform;

    // Trigger animation when element enters viewport
    inView(
      element,
      () => {
        animate(
          element,
          {
            opacity: config.animate.opacity,
            transform: config.animate.transform,
          },
          {
            duration: DURATION,
            delay,
            ease: EASE_OUT,
          }
        );
      },
      {
        amount: 0.1, // Trigger when 10% of element is visible
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
      child.style.transform = 'translateY(30px)';
    });

    // Trigger staggered animation when container enters viewport
    inView(
      container,
      () => {
        // Animate each child with staggered delay
        children.forEach((child, index) => {
          animate(
            child,
            {
              opacity: 1,
              transform: 'translateY(0px)',
            },
            {
              duration: DURATION,
              delay: index * 0.1, // 100ms stagger between children
              ease: EASE_OUT,
            }
          );
        });
      },
      {
        amount: 0.1, // Trigger when 10% of container is visible
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

        // Skip if empty anchor or not a valid selector (e.g., blob URLs)
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
