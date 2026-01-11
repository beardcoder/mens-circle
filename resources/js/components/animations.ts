/**
 * Animation Components
 *
 * Scroll animations are now handled purely via CSS using
 * animation-timeline: view() - no JavaScript needed!
 *
 * This file only contains smooth scroll functionality.
 */

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

/**
 * @deprecated Scroll animations are now CSS-only using animation-timeline: view()
 * This function is kept for backwards compatibility but does nothing.
 */
export function initScrollAnimations(): void {
  // CSS scroll-driven animations handle this automatically
  // No JavaScript needed - this is a no-op for backwards compatibility
}
