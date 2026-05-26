/**
 * Site Header
 *
 * Mobile nav drawer + scroll-state class + hero-overlap detection.
 * Lume handles listener cleanup; component state stays in closure variables.
 *
 * The drawer's open/close visuals live in CSS (clip-path + opacity +
 * stagger). This module just toggles `.open` / `.scrolled` /
 * `.header--on-hero` classes and manages body scroll lock + focus.
 */

import { prefersReducedMotion } from '@/utils/helpers';
import { defineComponent } from '@beardcoder/lume';

const SCROLL_THRESHOLD_PX = 50;
const CLOSE_ANIMATION_MS = 620;

export default defineComponent(({ root, part, parts, on, cleanup }) => {
  const heroEl = document.querySelector<HTMLElement>('.hero');
  const nav = part<HTMLElement>('nav');
  const toggle = part<HTMLButtonElement>('toggle');
  const navLinks = parts<HTMLAnchorElement>('nav-link');

  let isNavOpen = false;
  let isScrolled = window.scrollY > SCROLL_THRESHOLD_PX || !heroEl;
  let isOnHero = false;
  let scrollPosition = 0;
  let closeTimer: number | null = null;

  document.body.classList.toggle('has-hero', !!heroEl);
  document.body.classList.toggle('no-hero', !heroEl);

  const renderScrollState = (): void => {
    root.classList.toggle('scrolled', isScrolled);
    root.classList.toggle('header--on-hero', isOnHero);
  };

  const renderNavState = (): void => {
    nav.classList.toggle('open', isNavOpen);
    nav.setAttribute('aria-expanded', String(isNavOpen));
    toggle.classList.toggle('active', isNavOpen);
    toggle.setAttribute('aria-expanded', String(isNavOpen));
    toggle.setAttribute(
      'aria-label',
      isNavOpen ? 'Menü schließen' : 'Menü öffnen'
    );
  };

  const openNav = (): void => {
    if (isNavOpen) return;

    if (closeTimer !== null) {
      window.clearTimeout(closeTimer);
      closeTimer = null;
    }

    scrollPosition = window.scrollY;
    isNavOpen = true;
    document.body.classList.add('nav-open');
    document.body.style.top = `-${scrollPosition}px`;
    renderNavState();
  };

  const closeNav = (): void => {
    if (!isNavOpen) return;

    isNavOpen = false;
    renderNavState();

    const restore = (): void => {
      document.body.classList.remove('nav-open');
      document.body.style.top = '';
      window.scrollTo({ top: scrollPosition, left: 0, behavior: 'instant' });
      closeTimer = null;
    };

    if (prefersReducedMotion()) {
      restore();

      return;
    }

    closeTimer = window.setTimeout(restore, CLOSE_ANIMATION_MS);
  };

  // ─── Scroll state ──────────────────────────────────────────────────
  on(
    window,
    'scroll',
    () => {
      const next = window.scrollY > SCROLL_THRESHOLD_PX || !heroEl;

      if (next === isScrolled) return;
      isScrolled = next;
      renderScrollState();
    },
    { passive: true }
  );

  // ─── Hero overlap ──────────────────────────────────────────────────
  if (heroEl) {
    const heroObserver = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          const next = entry.isIntersecting && entry.intersectionRatio > 0.15;

          if (next === isOnHero) continue;
          isOnHero = next;
          renderScrollState();
        }
      },
      { threshold: [0, 0.15, 0.35, 0.5], rootMargin: '-10% 0px 0px 0px' }
    );

    heroObserver.observe(heroEl);
    cleanup(() => heroObserver.disconnect());
  }

  // ─── Interactions ──────────────────────────────────────────────────
  on(toggle, 'click', () => {
    if (isNavOpen) closeNav();
    else openNav();
  });

  for (const link of navLinks) {
    on(link, 'click', closeNav);
  }

  on(document, 'click', (event) => {
    if (isNavOpen && !root.contains(event.target as Node | null)) closeNav();
  });

  on(document, 'keydown', (event) => {
    if ((event as KeyboardEvent).key === 'Escape' && isNavOpen) closeNav();
  });

  // ─── Initial paint ─────────────────────────────────────────────────
  renderScrollState();
  renderNavState();

  cleanup(() => {
    if (closeTimer !== null) window.clearTimeout(closeTimer);
  });

  return {};
});
