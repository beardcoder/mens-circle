/**
 * Navigation Composables - Modern Functional Pattern
 * Handles mobile navigation and header scroll with smooth animations
 */

import { animate } from 'motion';

interface NavigationState {
  isOpen: boolean;
  scrollPosition: number;
}

/**
 * Mobile navigation composable
 * Handles mobile menu with smooth animations and accessibility
 */
export function useNavigation(): void {
  const navToggle = document.getElementById('navToggle');
  const nav = document.getElementById('nav');

  if (!navToggle || !nav) return;

  const state: NavigationState = {
    isOpen: false,
    scrollPosition: 0,
  };

  const animateLinks = async (direction: 'in' | 'out'): Promise<void> => {
    const navLinks = Array.from(
      nav.querySelectorAll<HTMLElement>('.nav__link, .nav__cta')
    );

    if (direction === 'in') {
      navLinks.forEach((link, index) => {
        animate(
          link,
          {
            opacity: [0, 1],
            transform: ['translateY(-12px)', 'translateY(0)'],
          } as any,
          {
            duration: 0.4,
            delay: index * 0.06,
            easing: [0.34, 1.56, 0.64, 1],
          } as any
        );
      });
    } else {
      navLinks.forEach((link) => {
        animate(
          link,
          { opacity: 0, transform: 'translateY(-8px)' } as any,
          { duration: 0.2, easing: [0.32, 0.72, 0, 1] } as any
        );
      });
    }
  };

  const updateAriaAttributes = (isOpen: boolean): void => {
    navToggle.setAttribute('aria-expanded', String(isOpen));
    navToggle.setAttribute(
      'aria-label',
      isOpen ? 'Menü schließen' : 'Menü öffnen'
    );
  };

  const open = async (): Promise<void> => {
    state.scrollPosition = window.scrollY;
    state.isOpen = true;

    nav.classList.add('open');
    navToggle.classList.add('active');
    document.body.classList.add('nav-open');
    document.body.style.top = `-${state.scrollPosition}px`;

    updateAriaAttributes(true);
    await animateLinks('in');
  };

  const close = async (): Promise<void> => {
    state.isOpen = false;
    await animateLinks('out');

    setTimeout(() => {
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
    }, 200);
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
