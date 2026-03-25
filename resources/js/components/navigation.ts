/**
 * Navigation Components
 * Mobile navigation, scroll header, and scroll-to-top using stitch-js
 */

import { defineComponent } from '@beardcoder/stitch-js';

interface NavigationOptions {
  toggleSelector: string;
  linkSelector: string;
}

interface NavigationState {
  isOpen: boolean;
  scrollPosition: number;
}

/**
 * Mobile navigation component
 * Attach to #nav — manages mobile menu with accessibility
 */
export const navigation = defineComponent<NavigationOptions>(
  {
    toggleSelector: '#navToggle',
    linkSelector: '.nav__link, .nav__cta',
  },
  (ctx) => {
    const { options: o } = ctx;
    const navToggle = document.getElementById(
      o.toggleSelector.replace('#', '')
    );

    if (!navToggle) return;

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

      ctx.el.classList.add('open');
      navToggle.classList.add('active');
      document.body.classList.add('nav-open');
      document.body.style.top = `-${state.scrollPosition}px`;

      updateAriaAttributes(true);
    };

    const close = (options: { restoreScroll?: boolean } = {}): void => {
      if (!state.isOpen) return;

      state.isOpen = false;

      ctx.el.classList.remove('open');
      navToggle.classList.remove('active');
      document.body.classList.remove('nav-open');
      document.body.style.top = '';

      if (options.restoreScroll ?? true) {
        window.scrollTo({
          top: state.scrollPosition,
          left: 0,
          behavior: 'instant',
        });
      }

      updateAriaAttributes(false);
    };

    const toggle = (): void => {
      if (state.isOpen) {
        close();
      } else {
        open();
      }
    };

    navToggle.addEventListener('click', toggle);
    ctx.onDestroy(() => navToggle.removeEventListener('click', toggle));

    ctx.on('click', o.linkSelector, () => {
      if (!state.isOpen) return;

      close({ restoreScroll: false });
    });

    const handleOutsideClick = (e: MouseEvent): void => {
      if (
        state.isOpen &&
        !ctx.el.contains(e.target as Node) &&
        !navToggle.contains(e.target as Node)
      ) {
        close();
      }
    };

    const handleEscape = (e: KeyboardEvent): void => {
      if (e.key === 'Escape' && state.isOpen) {
        close();
      }
    };

    document.addEventListener('click', handleOutsideClick);
    document.addEventListener('keydown', handleEscape);
    ctx.onDestroy(() => {
      document.removeEventListener('click', handleOutsideClick);
      document.removeEventListener('keydown', handleEscape);
    });
  }
);

interface ScrollHeaderOptions {
  scrollThreshold: number;
  heroSelector: string;
}

/**
 * Scroll header component
 * Attach to #header — updates appearance based on scroll position and hero presence
 */
export const scrollHeader = defineComponent<ScrollHeaderOptions>(
  {
    scrollThreshold: 50,
    heroSelector: '.hero',
  },
  (ctx) => {
    const { options: o } = ctx;
    const hasHero = Boolean(document.querySelector(o.heroSelector));

    document.body.classList.toggle('has-hero', hasHero);
    document.body.classList.toggle('no-hero', !hasHero);

    const updateScrollState = (): void => {
      const scrolled = window.scrollY > o.scrollThreshold || !hasHero;

      ctx.el.classList.toggle('scrolled', scrolled);
    };

    updateScrollState();
    window.addEventListener('scroll', updateScrollState, { passive: true });
    ctx.onDestroy(() =>
      window.removeEventListener('scroll', updateScrollState)
    );

    if (hasHero) {
      const hero = document.querySelector<HTMLElement>(o.heroSelector);

      if (!hero) return;

      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            ctx.el.classList.toggle(
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
      ctx.onDestroy(() => observer.disconnect());
    }
  }
);

interface ScrollToTopOptions {
  scrollThreshold: number;
  visibleClass: string;
}

/**
 * Scroll-to-top button component
 * Attach to #scrollToTop — shows/hides based on scroll position
 */
export const scrollToTop = defineComponent<ScrollToTopOptions>(
  {
    scrollThreshold: 400,
    visibleClass: 'visible',
  },
  (ctx) => {
    const { options: o } = ctx;

    const updateVisibility = (): void => {
      ctx.el.classList.toggle(
        o.visibleClass,
        window.scrollY > o.scrollThreshold
      );
    };

    updateVisibility();
    window.addEventListener('scroll', updateVisibility, { passive: true });
    ctx.onDestroy(() => window.removeEventListener('scroll', updateVisibility));

    ctx.on('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
);
