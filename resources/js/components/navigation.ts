/**
 * Navigation Components
 * Mobile navigation with anime.js stagger, scroll header, and scroll-to-top
 */

import { defineComponent } from '@stitch';
import { animate, stagger } from 'animejs';

interface NavigationOptions {
  toggleSelector: string;
  linkSelector: string;
}

interface NavigationState {
  isOpen: boolean;
  scrollPosition: number;
}

function prefersReducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * Mobile navigation component with anime.js stagger animations
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

    const isMobile = (): boolean =>
      window.matchMedia('(width <= 900px)').matches;

    const animateLinksIn = (): void => {
      if (prefersReducedMotion() || !isMobile()) return;

      const links = ctx.el.querySelectorAll<HTMLElement>(o.linkSelector);

      if (links.length === 0) return;

      // Reset initial state for animation
      links.forEach((link) => {
        link.style.opacity = '0';
        link.style.transform = 'translateY(24px) scale(0.98)';
        link.style.filter = 'blur(4px)';
      });

      animate(links, {
        opacity: [0, 1],
        translateY: ['24px', '0px'],
        scale: [0.98, 1],
        filter: ['blur(4px)', 'blur(0px)'],
        duration: 500,
        delay: stagger(70, { start: 100 }),
        ease: 'outQuint',
        onComplete: () => {
          links.forEach((link) => {
            link.style.filter = '';
            link.style.transform = '';
            link.style.opacity = '';
          });
        },
      });
    };

    const animateLinksOut = (): void => {
      if (prefersReducedMotion() || !isMobile()) return;

      const links = ctx.el.querySelectorAll<HTMLElement>(o.linkSelector);

      if (links.length === 0) return;

      animate(links, {
        opacity: [1, 0],
        translateY: ['0px', '-12px'],
        scale: [1, 0.97],
        duration: 200,
        delay: stagger(30),
        ease: 'inCubic',
      });
    };

    const open = (): void => {
      state.scrollPosition = window.scrollY;
      state.isOpen = true;

      ctx.el.classList.add('open');
      navToggle.classList.add('active');
      document.body.classList.add('nav-open');
      document.body.style.top = `-${state.scrollPosition}px`;

      updateAriaAttributes(true);
      animateLinksIn();
    };

    const close = (options: { restoreScroll?: boolean } = {}): void => {
      if (!state.isOpen) return;

      state.isOpen = false;
      animateLinksOut();

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
