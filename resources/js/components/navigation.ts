/**
 * Navigation Alpine Components
 *
 * `siteHeader` drives the fixed navigation: open/close drawer, scroll-state
 * toggle, hero-overlap detection. `scrollToTop` powers the floating button.
 *
 * Both share a single AbortController per instance — calling `destroy()`
 * aborts every listener and observer in one shot.
 */

import type { AlpineMagics } from '@/types/alpine';

const SCROLL_THRESHOLD = 50;
const SCROLL_TOP_THRESHOLD = 400;

export function siteHeader() {
  const controller = new AbortController();
  let heroObserver: IntersectionObserver | null = null;

  return {
    isNavOpen: false,
    isScrolled: false,
    isOnHero: false,
    scrollPosition: 0,

    init(this: AlpineMagics & SiteHeaderState) {
      const heroEl = document.querySelector<HTMLElement>('.hero');

      document.body.classList.toggle('has-hero', !!heroEl);
      document.body.classList.toggle('no-hero', !heroEl);

      const updateScroll = (): void => {
        this.isScrolled = window.scrollY > SCROLL_THRESHOLD || !heroEl;
      };

      updateScroll();

      const { signal } = controller;

      window.addEventListener('scroll', updateScroll, {
        passive: true,
        signal,
      });

      if (heroEl) {
        heroObserver = new IntersectionObserver(
          (entries) => {
            for (const entry of entries) {
              this.isOnHero =
                entry.isIntersecting && entry.intersectionRatio > 0.15;
            }
          },
          { threshold: [0, 0.15, 0.35, 0.5], rootMargin: '-10% 0px 0px 0px' }
        );

        heroObserver.observe(heroEl);
      }

      document.addEventListener(
        'click',
        (event: MouseEvent) => {
          if (this.isNavOpen && !this.$el.contains(event.target as Node)) {
            this.closeNav();
          }
        },
        { signal }
      );

      document.addEventListener(
        'keydown',
        (event: KeyboardEvent) => {
          if (event.key === 'Escape' && this.isNavOpen) this.closeNav();
        },
        { signal }
      );
    },

    openNav(this: SiteHeaderState) {
      this.scrollPosition = window.scrollY;
      this.isNavOpen = true;
      document.body.classList.add('nav-open');
      document.body.style.top = `-${this.scrollPosition}px`;
    },

    closeNav(this: SiteHeaderState) {
      if (!this.isNavOpen) return;

      this.isNavOpen = false;
      document.body.classList.remove('nav-open');
      document.body.style.top = '';

      window.scrollTo({
        top: this.scrollPosition,
        left: 0,
        behavior: 'instant',
      });
    },

    closeNavImmediate(this: SiteHeaderState) {
      if (!this.isNavOpen) return;

      this.isNavOpen = false;
      document.body.classList.remove('nav-open');
      document.body.style.top = '';
    },

    toggleNav(this: SiteHeaderState) {
      if (this.isNavOpen) this.closeNav();
      else this.openNav();
    },

    destroy() {
      controller.abort();
      heroObserver?.disconnect();
      heroObserver = null;
    },
  };
}

interface SiteHeaderState {
  isNavOpen: boolean;
  isScrolled: boolean;
  isOnHero: boolean;
  scrollPosition: number;
  closeNav: () => void;
  openNav: () => void;
}

export function scrollToTop() {
  const controller = new AbortController();

  return {
    isVisible: false,

    init(this: { isVisible: boolean }) {
      const update = (): void => {
        this.isVisible = window.scrollY > SCROLL_TOP_THRESHOLD;
      };

      update();
      window.addEventListener('scroll', update, {
        passive: true,
        signal: controller.signal,
      });
    },

    scrollUp() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    destroy() {
      controller.abort();
    },
  };
}
