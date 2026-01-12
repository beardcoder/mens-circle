/**
 * Accordion Component with Motion.dev Animations
 *
 * Provides smooth, performant accordion animations using Motion.dev
 * with spring-like easing for natural, organic motion.
 *
 * Works with native <details>/<summary> elements
 *
 * @see https://motion.dev/docs/animate
 */

import { animate } from 'motion';

/**
 * Easing curves optimized for natural accordion motion
 *
 * These bezier curves simulate spring-like physics
 */
const EASING = {
  // Spring-like expansion - quick start, smooth settle with slight overshoot
  expand: [0.22, 1.2, 0.36, 1] as [number, number, number, number],
  // Smooth collapse - natural deceleration
  collapse: [0.32, 0.72, 0, 1] as [number, number, number, number],
  // Content reveal - gentle spring feel
  content: [0.34, 1.56, 0.64, 1] as [number, number, number, number],
  // Quick fade - smooth deceleration
  fade: [0, 0, 0.2, 1] as [number, number, number, number],
  // Quick fade out
  fadeOut: [0.4, 0, 1, 1] as [number, number, number, number],
};

/**
 * Timing configuration
 */
const TIMING = {
  // Duration for height animation
  heightDuration: 0.5,
  // Duration for opacity animation
  fadeDuration: 0.35,
  // Content reveal delay
  contentDelay: 0.06,
};

/**
 * Check if user prefers reduced motion
 */
function prefersReducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * Animate accordion open with smooth spring-like motion
 */
function animateOpen(content: HTMLElement, inner: HTMLElement): Promise<void> {
  return new Promise((resolve) => {
    // Get the natural height of the content
    const height = inner.offsetHeight;

    // Start with content invisible
    inner.style.opacity = '0';
    inner.style.transform = 'translateY(-8px)';

    // Animate height with spring-like easing for smooth expansion
    const heightAnimation = animate(
      content,
      { height: ['0px', `${height}px`] },
      {
        duration: TIMING.heightDuration,
        ease: EASING.expand,
      }
    );

    // Fade in container
    animate(
      content,
      { opacity: [0, 1] },
      {
        duration: TIMING.fadeDuration,
        ease: EASING.fade,
      }
    );

    // Animate inner content with slight delay for stagger effect
    setTimeout(() => {
      // Fade in inner content
      animate(
        inner,
        { opacity: [0, 1] },
        {
          duration: TIMING.fadeDuration,
          ease: EASING.fade,
        }
      );

      // Spring the inner content into place
      animate(
        inner,
        { transform: ['translateY(-8px)', 'translateY(0px)'] },
        {
          duration: TIMING.heightDuration * 0.9,
          ease: EASING.content,
        }
      );
    }, TIMING.contentDelay * 1000);

    // Resolve when height animation completes
    heightAnimation.then(() => {
      // Remove fixed height to allow content reflow
      content.style.height = 'auto';
      resolve();
    });
  });
}

/**
 * Animate accordion close with smooth motion
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

    // Force reflow
    void content.offsetHeight;

    // Fade out inner content first (quick)
    animate(
      inner,
      { opacity: [1, 0] },
      {
        duration: TIMING.fadeDuration * 0.6,
        ease: EASING.fadeOut,
      }
    );

    // Slide inner content up slightly
    animate(
      inner,
      { transform: ['translateY(0px)', 'translateY(-6px)'] },
      {
        duration: TIMING.fadeDuration * 0.7,
        ease: EASING.fadeOut,
      }
    );

    // Fade out container
    animate(
      content,
      { opacity: [1, 0] },
      {
        duration: TIMING.fadeDuration,
        delay: TIMING.fadeDuration * 0.15,
        ease: EASING.fadeOut,
      }
    );

    // Animate height with smooth collapse
    animate(
      content,
      { height: [`${height}px`, '0px'] },
      {
        duration: TIMING.heightDuration * 0.85,
        ease: EASING.collapse,
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
    inner.style.transform = 'translateY(0)';
  } else {
    content.style.height = '0';
    content.style.opacity = '0';
    inner.style.opacity = '0';
    inner.style.transform = 'translateY(-8px)';
  }
}

/**
 * Initialize accordion for reduced motion users
 */
function initAccordionItemReducedMotion(details: HTMLDetailsElement): void {
  const content = details.querySelector<HTMLElement>(
    '.accordion-item__content, .faq-item__answer'
  );
  const inner = details.querySelector<HTMLElement>(
    '.accordion-item__body, .faq-item__answer-inner'
  );

  if (!content) return;

  // Set initial state
  const setVisibility = (open: boolean) => {
    if (open) {
      content.style.height = 'auto';
      content.style.opacity = '1';
      content.style.display = 'block';

      if (inner) {
        inner.style.opacity = '1';
        inner.style.transform = 'none';
      }
    } else {
      content.style.height = '0';
      content.style.opacity = '0';
      content.style.display = 'none';

      if (inner) {
        inner.style.opacity = '0';
      }
    }
  };

  // Apply initial state
  setVisibility(details.open);

  // Listen for toggle events
  details.addEventListener('toggle', () => {
    setVisibility(details.open);
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
