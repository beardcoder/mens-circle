/**
 * Scroll Animations Component
 * Handles fade-in and stagger animations using IntersectionObserver
 */
export function initScrollAnimations(): void {
  const fadeElements = document.querySelectorAll<HTMLElement>('.fade-in');
  const staggerElements =
    document.querySelectorAll<HTMLElement>('.stagger-children');

  const allAnimatedElements = [...fadeElements, ...staggerElements];

  if (!allAnimatedElements.length) return;

  // Check if IntersectionObserver is supported
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px',
      }
    );

    allAnimatedElements.forEach((el) => observer.observe(el));
  } else {
    // Fallback: show all elements immediately
    allAnimatedElements.forEach((el) => el.classList.add('visible'));
  }
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
