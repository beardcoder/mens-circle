/**
 * Accordion Component with Motion.dev Animations
 *
 * Provides smooth, performant accordion animations using Motion.dev
 * Works with native <details>/<summary> elements
 *
 * @see https://motion.dev/docs/inview
 */

import { animate } from 'motion';

/**
 * Animation configuration
 */
const ANIMATION_CONFIG = {
  duration: 0.4,
  ease: [0.16, 1, 0.3, 1] as [number, number, number, number],
};

/**
 * Check if user prefers reduced motion
 */
function prefersReducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * Animate accordion open
 */
function animateOpen(content: HTMLElement, inner: HTMLElement): Promise<void> {
  return new Promise((resolve) => {
    // Get the natural height of the content
    const height = inner.offsetHeight;

    // Animate height and opacity
    animate(
      content,
      {
        height: ['0px', `${height}px`],
        opacity: [0, 1],
      },
      {
        duration: ANIMATION_CONFIG.duration,
        ease: ANIMATION_CONFIG.ease,
      }
    ).then(() => {
      // Remove fixed height after animation to allow content reflow
      content.style.height = 'auto';
      resolve();
    });

    // Animate inner content with slight delay for stagger effect
    animate(
      inner,
      {
        opacity: [0, 1],
        transform: ['translateY(-10px)', 'translateY(0px)'],
      },
      {
        duration: ANIMATION_CONFIG.duration * 0.8,
        delay: ANIMATION_CONFIG.duration * 0.2,
        ease: ANIMATION_CONFIG.ease,
      }
    );
  });
}

/**
 * Animate accordion close
 */
function animateClose(
  details: HTMLDetailsElement,
  content: HTMLElement,
  inner: HTMLElement
): Promise<void> {
  return new Promise((resolve) => {
    // Get current height before animating
    const height = content.offsetHeight;

    // Set explicit height for animation
    content.style.height = `${height}px`;

    // Force reflow (void to satisfy linter)
    void content.offsetHeight;

    // Animate inner content first
    animate(
      inner,
      {
        opacity: [1, 0],
        transform: ['translateY(0px)', 'translateY(-10px)'],
      },
      {
        duration: ANIMATION_CONFIG.duration * 0.6,
        ease: ANIMATION_CONFIG.ease,
      }
    );

    // Animate height and opacity
    animate(
      content,
      {
        height: [`${height}px`, '0px'],
        opacity: [1, 0],
      },
      {
        duration: ANIMATION_CONFIG.duration,
        ease: ANIMATION_CONFIG.ease,
      }
    ).then(() => {
      // Actually close the details element after animation
      details.removeAttribute('open');
      content.style.height = '';
      resolve();
    });
  });
}

/**
 * Initialize accordion animations for a single details element
 */
function initAccordionItem(details: HTMLDetailsElement): void {
  const summary = details.querySelector('summary');
  const content = details.querySelector<HTMLElement>(
    '.accordion-item__content, .faq-item__answer'
  );
  const inner = details.querySelector<HTMLElement>(
    '.accordion-item__body, .faq-item__answer-inner'
  );

  if (!summary || !content || !inner) return;

  // Mark content as animated
  content.setAttribute('data-accordion-content', '');

  // Track animation state
  let isAnimating = false;

  // Handle click on summary
  summary.addEventListener('click', (e) => {
    e.preventDefault();

    if (isAnimating) return;

    isAnimating = true;

    if (details.open) {
      // Close animation
      animateClose(details, content, inner).then(() => {
        isAnimating = false;
      });
    } else {
      // Open the details first (needed to measure height)
      details.setAttribute('open', '');

      // Then animate
      animateOpen(content, inner).then(() => {
        isAnimating = false;
      });
    }
  });

  // Handle keyboard accessibility
  summary.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      summary.click();
    }
  });

  // Set initial state if already open
  if (details.open) {
    content.style.height = 'auto';
    content.style.opacity = '1';
    inner.style.opacity = '1';
  } else {
    content.style.height = '0';
    content.style.opacity = '0';
    inner.style.opacity = '0';
  }
}

/**
 * Initialize accordion animations for reduced motion users
 */
function initAccordionItemReducedMotion(details: HTMLDetailsElement): void {
  const content = details.querySelector<HTMLElement>(
    '.accordion-item__content, .faq-item__answer'
  );

  if (!content) return;

  // For reduced motion, just show/hide without animation
  if (details.open) {
    content.style.height = 'auto';
    content.style.display = 'block';
  } else {
    content.style.height = '0';
    content.style.display = 'none';
  }

  details.addEventListener('toggle', () => {
    if (details.open) {
      content.style.height = 'auto';
      content.style.display = 'block';
    } else {
      content.style.height = '0';
      content.style.display = 'none';
    }
  });
}

/**
 * Initialize all accordions on the page
 */
export function initAccordions(): void {
  const accordions = document.querySelectorAll<HTMLDetailsElement>(
    '.accordion-item, .faq-item'
  );

  if (accordions.length === 0) return;

  const reduceMotion = prefersReducedMotion();

  accordions.forEach((details) => {
    if (reduceMotion) {
      initAccordionItemReducedMotion(details);
    } else {
      initAccordionItem(details);
    }
  });
}

/**
 * Initialize FAQ component
 * Alias for initAccordions for backwards compatibility
 */
export function initFAQ(): void {
  initAccordions();
}
