/**
 * Animation Components - Modern Implementation
 * Using Motion One for performant scroll-triggered animations
 * Replaces legacy animation patterns with composable approach
 */

import { animate, inView } from 'motion';

interface AnimationConfig {
  initial: Record<string, number>;
  animate: Record<string, number>;
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

const EASING = {
  spring: [0.22, 1.2, 0.36, 1] as const,
  smooth: [0.25, 1, 0.5, 1] as const,
  decelerate: [0, 0, 0.2, 1] as const,
  gentle: [0.34, 1.56, 0.64, 1] as const,
};

const TIMING = {
  transformDuration: 0.6,
  opacityDuration: 0.4,
  staggerDelay: 0.06,
  viewportAmount: 0.1,
};

const DELAY_MAP: Record<string, number> = {
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

function prefersReducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function getAnimationType(element: Element): string | null {
  return (
    Object.keys(ANIMATION_CONFIGS).find((cls) =>
      element.classList.contains(cls)
    ) ?? null
  );
}

function getDelay(element: Element): number {
  const delayClass = Object.keys(DELAY_MAP).find((cls) =>
    element.classList.contains(cls)
  );
  return delayClass ? DELAY_MAP[delayClass] : 0;
}

function buildTransform(config: Record<string, number>): string {
  const transforms: string[] = [];

  if (config.y !== undefined) transforms.push(`translateY(${config.y}px)`);
  if (config.x !== undefined) transforms.push(`translateX(${config.x}px)`);
  if (config.scale !== undefined) transforms.push(`scale(${config.scale})`);

  return transforms.length > 0 ? transforms.join(' ') : 'none';
}

export function initScrollAnimations(): void {
  if (prefersReducedMotion()) {
    showAllElementsImmediately();
    return;
  }

  initFadeAnimations();
  initStaggerAnimations();
}

function showAllElementsImmediately(): void {
  const selectors = [
    ...Object.keys(ANIMATION_CONFIGS).map((cls) => `.${cls}`),
    '.stagger-children > *',
  ].join(', ');

  document.querySelectorAll<HTMLElement>(selectors).forEach((el) => {
    el.style.opacity = '1';
    el.style.transform = 'none';
  });
}

function initFadeAnimations(): void {
  const selectors = Object.keys(ANIMATION_CONFIGS)
    .map((cls) => `.${cls}:not(.stagger-children > *)`)
    .join(', ');

  document.querySelectorAll<HTMLElement>(selectors).forEach((element) => {
    const animationType = getAnimationType(element);
    if (!animationType) return;

    const config = ANIMATION_CONFIGS[animationType];
    const delay = getDelay(element);

    element.style.opacity = String(config.initial.opacity);
    element.style.transform = buildTransform(config.initial);

    inView(
      element,
      () => {
        animate(
          element,
          { opacity: config.animate.opacity } as any,
          {
            duration: TIMING.opacityDuration,
            delay,
            easing: EASING.decelerate,
          } as any
        );

        animate(
          element,
          { transform: buildTransform(config.animate) } as any,
          {
            duration: TIMING.transformDuration,
            delay,
            easing: EASING.spring,
          } as any
        );
      },
      { amount: TIMING.viewportAmount }
    );
  });
}

function initStaggerAnimations(): void {
  document
    .querySelectorAll<HTMLElement>('.stagger-children')
    .forEach((container) => {
      const children = Array.from(container.children) as HTMLElement[];
      if (children.length === 0) return;

      children.forEach((child) => {
        child.style.opacity = '0';
        child.style.transform = 'translateY(20px)';
      });

      inView(
        container,
        () => {
          children.forEach((child, index) => {
            const delay = index * TIMING.staggerDelay;

            animate(
              child,
              { opacity: 1 } as any,
              {
                duration: TIMING.opacityDuration,
                delay,
                easing: EASING.decelerate,
              } as any
            );

            animate(
              child,
              { transform: 'translateY(0px)' } as any,
              {
                duration: TIMING.transformDuration,
                delay,
                easing: EASING.gentle,
              } as any
            );
          });
        },
        { amount: TIMING.viewportAmount }
      );
    });
}

/**
 * Smooth Scroll for Anchor Links
 * Uses native smooth scrolling with header offset
 */
export function initSmoothScroll(): void {
  document
    .querySelectorAll<HTMLAnchorElement>('a[href^="#"]')
    .forEach((anchor) => {
      anchor.addEventListener('click', function (e) {
        const targetId = this.getAttribute('href');

        if (!targetId || targetId === '#' || !targetId.startsWith('#')) {
          return;
        }

        try {
          const target = document.querySelector<HTMLElement>(targetId);
          if (!target) return;

          e.preventDefault();
          const headerHeight =
            document.getElementById('header')?.offsetHeight ?? 0;
          const targetPosition =
            target.getBoundingClientRect().top + window.scrollY - headerHeight - 20;

          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth',
          });
        } catch {
          // Invalid selector - ignore
        }
      });
    });
}

