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
const FALLBACK_HEADER_OFFSET_PX = 100;

/**
 * Resolve the offset (in px) that anchored scrolling must leave below the
 * fixed header, derived from the `--header-clearance` custom property.
 */
const headerOffset = (): number => {
  const raw = getComputedStyle(document.documentElement).getPropertyValue(
    '--header-clearance'
  );
  const parsed = Number.parseInt(raw, 10);

  return Number.isFinite(parsed) ? parsed : FALLBACK_HEADER_OFFSET_PX;
};

/**
 * Return the fragment of a link that points to an anchor on the current
 * page, or `null` when the link navigates elsewhere (different origin or
 * path, opens in a new tab, or has no fragment).
 */
const samePageHash = (link: HTMLAnchorElement): string | null => {
  if (link.target && link.target !== '_self') return null;

  let url: URL;

  try {
    url = new URL(link.href, window.location.href);
  } catch {
    return null;
  }

  if (url.origin !== window.location.origin) return null;
  if (url.pathname !== window.location.pathname) return null;
  if (url.hash === '' || url.hash === '#') return null;

  return url.hash;
};

export default defineComponent(({ root, part, parts, on, cleanup }) => {
  const heroEl = document.querySelector<HTMLElement>('.hero');
  const nav = part<HTMLElement>('nav');
  const toggle = part<HTMLButtonElement>('toggle');
  const navLinks = parts<HTMLAnchorElement>('nav-link');

  /**
   * Smoothly scroll the in-page element matching `hash` into view, offset
   * for the fixed header, and sync the URL fragment. Returns whether a
   * matching target was found.
   */
  const scrollToAnchor = (hash: string): boolean => {
    const id = decodeURIComponent(hash.replace(/^#/, ''));
    const target = id === '' ? null : document.getElementById(id);

    if (target === null) return false;

    const top =
      target.getBoundingClientRect().top + window.scrollY - headerOffset();

    window.scrollTo({
      top: Math.max(top, 0),
      left: 0,
      behavior: prefersReducedMotion() ? 'instant' : 'smooth',
    });

    history.pushState(null, '', `#${id}`);

    return true;
  };

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

  /**
   * Close the drawer. When `targetHash` points to an in-page anchor, scroll
   * to it once the body scroll lock is released instead of restoring the
   * pre-open scroll position.
   */
  const closeNav = (targetHash: string | null = null): void => {
    if (!isNavOpen) return;

    isNavOpen = false;
    renderNavState();

    const restore = (): void => {
      document.body.classList.remove('nav-open');
      document.body.style.top = '';

      if (targetHash !== null && scrollToAnchor(targetHash)) {
        closeTimer = null;

        return;
      }

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
    on(link, 'click', (event) => {
      const hash = samePageHash(link);

      // Non-anchor links navigate normally; just dismiss an open drawer.
      if (hash === null) {
        closeNav();

        return;
      }

      // Own the scroll for in-page anchors so the fixed header is cleared
      // and the mobile drawer's scroll-lock restore doesn't snap us back.
      (event as MouseEvent).preventDefault();

      if (isNavOpen) {
        closeNav(hash);
      } else {
        scrollToAnchor(hash);
      }
    });
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
