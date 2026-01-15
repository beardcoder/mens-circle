/**
 * Navigation Composables - Modern Functional Pattern
 * Handles mobile navigation with CSS-only animations
 */

interface NavigationState {
  isOpen: boolean;
  scrollPosition: number;
}

/**
 * Mobile navigation composable
 * Handles mobile menu with CSS-only animations and accessibility
 */
export function useNavigation(): void {
  const navToggle = document.getElementById('navToggle');
  const nav = document.getElementById('nav');

  if (!navToggle || !nav) return;

  const state: NavigationState = {
    isOpen: false,
    scrollPosition: 0,
  };

  const updateAriaAttributes = (isOpen: boolean): void => {
    navToggle.setAttribute('aria-expanded', String(isOpen));
    navToggle.setAttribute(
      'aria-label',
      isOpen ? 'Menü schließen' : 'Menü öffnen'
    );
  };

  const open = (): void => {
    state.scrollPosition = window.scrollY;
    state.isOpen = true;

    nav.classList.add('open');
    navToggle.classList.add('active');
    document.body.classList.add('nav-open');
    document.body.style.top = `-${state.scrollPosition}px`;

    updateAriaAttributes(true);
  };

  const close = (): void => {
    state.isOpen = false;

    nav.classList.remove('open');
    navToggle.classList.remove('active');
    document.body.classList.remove('nav-open');
    document.body.style.top = '';

    window.scrollTo({
      top: state.scrollPosition,
      left: 0,
      behavior: 'instant',
    });

    updateAriaAttributes(false);
  };

  const toggle = (): void => {
    if (state.isOpen) {
      close();
    } else {
      open();
    }
  };

  // Event listeners
  navToggle.addEventListener('click', toggle);

  const navLinks = nav.querySelectorAll<HTMLAnchorElement>(
    '.nav__link, .nav__cta'
  );

  navLinks.forEach((link) => {
    link.addEventListener('click', () => close());
  });

  document.addEventListener('click', (e) => {
    if (
      state.isOpen &&
      !nav.contains(e.target as Node) &&
      !navToggle.contains(e.target as Node)
    ) {
      close();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && state.isOpen) {
      close();
    }
  });
}

/**
 * Header scroll effect composable
 * Updates header appearance based on scroll position and hero presence
 */
export function useScrollHeader(): void {
  const header = document.getElementById('header');

  if (!header) return;

  const hasHero = Boolean(document.querySelector('.hero'));

  document.body.classList.toggle('has-hero', hasHero);
  document.body.classList.toggle('no-hero', !hasHero);

  const updateScrollState = (): void => {
    const scrolled = window.scrollY > 50 || !hasHero;

    header.classList.toggle('scrolled', scrolled);
  };

  updateScrollState();
  window.addEventListener('scroll', updateScrollState, { passive: true });

  if (hasHero) {
    const hero = document.querySelector<HTMLElement>('.hero');

    if (!hero) return;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          header.classList.toggle(
            'header--on-hero',
            entry.isIntersecting && entry.intersectionRatio > 0.15
          );
        });
      },
      {
        threshold: [0, 0.15, 0.35, 0.5],
        rootMargin: '-10% 0px 0px 0px',
      }
    );

    observer.observe(hero);
  }
}
