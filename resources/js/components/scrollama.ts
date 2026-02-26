/**
 * Scrollama Composables
 * Scroll-driven animations and section tracking powered by Scrollama
 */

import scrollama from 'scrollama';

/* ============================================
   Animation Configuration
   ============================================ */

const ANIMATION_DURATION = 800;
const ANIMATION_STAGGER_DELAY = 150;
const ANIMATION_EASING = 'cubic-bezier(0.16, 0.6, 0.4, 1)';

type Direction = 'up' | 'down' | 'left' | 'right' | 'scale' | 'default';

interface AnimationTransform {
  from: { opacity: number; transform: string };
  to: { opacity: number; transform: string };
}

function getTransformForDirection(direction: Direction): AnimationTransform {
  const transforms: Record<Direction, string> = {
    default: 'translateY(20px)',
    up: 'translateY(20px)',
    down: 'translateY(-20px)',
    left: 'translateX(-20px)',
    right: 'translateX(20px)',
    scale: 'scale(0.97)',
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

function animateElement(element: HTMLElement, delay: number): void {
  const direction = getDirection(element);
  const { from, to } = getTransformForDirection(direction);

  element.style.willChange = 'opacity, transform';

  const animation = element.animate(
    [
      { opacity: from.opacity, transform: from.transform },
      { opacity: to.opacity, transform: to.transform },
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
}

function setInitialState(element: HTMLElement): void {
  const direction = getDirection(element);
  const { from } = getTransformForDirection(direction);

  element.style.opacity = String(from.opacity);
  element.style.transform = from.transform;
}

/* ============================================
   useScrollAnimations
   Triggers fade-in animations on scroll using Scrollama
   ============================================ */

export function useScrollAnimations(): void {
  const prefersReducedMotion = window.matchMedia(
    '(prefers-reduced-motion: reduce)'
  ).matches;

  if (prefersReducedMotion) return;

  const fadeElements = document.querySelectorAll<HTMLElement>(
    '.fade-in, .fade-in-up, .fade-in-down, .fade-in-left, .fade-in-right, .fade-in-scale'
  );

  const staggerContainers =
    document.querySelectorAll<HTMLElement>('.stagger-children');

  if (fadeElements.length === 0 && staggerContainers.length === 0) return;

  // Set initial hidden state for individual fade elements
  fadeElements.forEach((el) => {
    // Skip elements inside stagger containers (they're handled separately)
    if (el.closest('.stagger-children')) return;
    setInitialState(el);
  });

  // Set initial hidden state for stagger children
  staggerContainers.forEach((container) => {
    const children = container.children;

    for (let i = 0; i < children.length; i++) {
      const child = children[i] as HTMLElement;

      child.style.opacity = '0';
      child.style.transform = 'translateY(12px)';
    }
  });

  // Scrollama for individual fade-in elements
  if (fadeElements.length > 0) {
    const filteredElements = Array.from(fadeElements).filter(
      (el) => !el.closest('.stagger-children')
    );

    if (filteredElements.length > 0) {
      scrollama()
        .setup({
          step: filteredElements,
          offset: 0.8,
          once: true,
        })
        .onStepEnter(({ element }) => {
          const delay = getDelay(element);

          animateElement(element, delay);
        });
    }
  }

  // Scrollama for stagger containers
  if (staggerContainers.length > 0) {
    scrollama()
      .setup({
        step: Array.from(staggerContainers),
        offset: 0.8,
        once: true,
      })
      .onStepEnter(({ element }) => {
        const children = element.children;

        for (let i = 0; i < children.length; i++) {
          const child = children[i] as HTMLElement;

          child.style.willChange = 'opacity, transform';

          const animation = child.animate(
            [
              { opacity: 0, transform: 'translateY(12px)' },
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
      });
  }
}

/* ============================================
   useActiveSection
   Tracks which section is in view and highlights the nav link
   ============================================ */

const SECTION_IDS = ['ueber', 'reise', 'faq', 'newsletter'];

export function useActiveSection(): void {
  const sections = SECTION_IDS.map((id) => document.getElementById(id)).filter(
    Boolean
  ) as HTMLElement[];

  if (sections.length === 0) return;

  const navLinks = document.querySelectorAll<HTMLAnchorElement>(
    '.nav__link[href*="#"]'
  );

  if (navLinks.length === 0) return;

  const setActive = (sectionId: string | null): void => {
    navLinks.forEach((link) => {
      const href = link.getAttribute('href') ?? '';
      const isActive = sectionId !== null && href.endsWith(`#${sectionId}`);

      link.classList.toggle('nav__link--active', isActive);
    });
  };

  scrollama()
    .setup({
      step: sections,
      offset: 0.4,
    })
    .onStepEnter(({ element }) => {
      setActive(element.id);
    })
    .onStepExit(({ element, direction }) => {
      // When scrolling back up past the first section, remove all active states
      if (direction === 'up' && element.id === sections[0]?.id) {
        setActive(null);
      }
    });
}

/* ============================================
   useJourneyProgress
   Animates journey step numbers and a progress line as user scrolls
   ============================================ */

export function useJourneyProgress(): void {
  const prefersReducedMotion = window.matchMedia(
    '(prefers-reduced-motion: reduce)'
  ).matches;

  if (prefersReducedMotion) return;

  const steps = document.querySelectorAll<HTMLElement>('.journey__step');

  if (steps.length === 0) return;

  // Set initial state - step numbers start dimmed
  steps.forEach((step) => {
    const number = step.querySelector<HTMLElement>('.journey__step-number');

    if (number) {
      number.style.transition =
        'opacity 0.6s cubic-bezier(0.16, 0.6, 0.4, 1), transform 0.6s cubic-bezier(0.16, 0.6, 0.4, 1)';
      number.style.opacity = '0.08';
      number.style.transform = 'scale(0.95)';
    }
  });

  scrollama()
    .setup({
      step: Array.from(steps),
      offset: 0.6,
      once: true,
    })
    .onStepEnter(({ element }) => {
      const number = element.querySelector<HTMLElement>(
        '.journey__step-number'
      );

      if (number) {
        number.style.opacity = '0.25';
        number.style.transform = 'scale(1)';
      }

      element.classList.add('journey__step--active');
    });
}
