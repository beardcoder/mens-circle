/**
 * Site Header
 *
 * Mobile nav drawer + scroll-state toggle + hero-overlap detection.
 * Vanilla TS, no framework. Driven by `[data-component="site-header"]`
 * on the `<header>` element.
 *
 * The drawer's open/close animation lives entirely in CSS (clip-path +
 * opacity + transition-delay stagger). This class just toggles the
 * `.open` class and manages body scroll lock, focus restoration, and
 * the close-on-link/outside-click/escape interactions.
 */

import { prefersReducedMotion } from '@/utils/helpers';
import { mountAll, ReactiveHost } from '@/lib/reactive-host';

const SCROLL_THRESHOLD_PX = 50;
const CLOSE_ANIMATION_MS = 620;

class SiteHeader extends ReactiveHost {
  private isNavOpen = false;
  private isScrolled = false;
  private isOnHero = false;
  private scrollPosition = 0;
  private heroEl: HTMLElement | null = null;
  private nav: HTMLElement | null = null;
  private toggle: HTMLButtonElement | null = null;
  private heroObserver: IntersectionObserver | null = null;
  private closeTimer: number | null = null;

  protected setup(): void {
    this.heroEl = document.querySelector<HTMLElement>('.hero');
    this.nav = this.query('.nav');
    this.toggle = this.query<HTMLButtonElement>('.nav-toggle');

    document.body.classList.toggle('has-hero', !!this.heroEl);
    document.body.classList.toggle('no-hero', !this.heroEl);

    this.onWindow(
      'scroll',
      () => {
        const next = window.scrollY > SCROLL_THRESHOLD_PX || !this.heroEl;

        if (next === this.isScrolled) return;

        this.isScrolled = next;
        this.renderScrollState();
      },
      { passive: true }
    );

    this.isScrolled = window.scrollY > SCROLL_THRESHOLD_PX || !this.heroEl;

    if (this.heroEl) {
      this.heroObserver = new IntersectionObserver(
        (entries) => {
          for (const entry of entries) {
            const next = entry.isIntersecting && entry.intersectionRatio > 0.15;

            if (next === this.isOnHero) continue;

            this.isOnHero = next;
            this.renderScrollState();
          }
        },
        { threshold: [0, 0.15, 0.35, 0.5], rootMargin: '-10% 0px 0px 0px' }
      );

      this.heroObserver.observe(this.heroEl);
    }

    this.on(this.toggle, 'click', () => this.toggleNav());

    // Close drawer when a link is tapped. The drawer animates out
    // before the navigation happens so the transition is visible.
    for (const link of this.queryAll<HTMLAnchorElement>('.nav a')) {
      this.on(link, 'click', () => this.close());
    }

    this.onDocument('click', (event) => {
      if (this.isNavOpen && !this.root.contains(event.target as Node | null)) {
        this.close();
      }
    });

    this.onDocument('keydown', (event) => {
      if (event.key === 'Escape' && this.isNavOpen) this.close();
    });

    this.renderScrollState();
    this.renderNavState();
  }

  protected teardown(): void {
    this.heroObserver?.disconnect();
    this.heroObserver = null;

    if (this.closeTimer !== null) {
      window.clearTimeout(this.closeTimer);
      this.closeTimer = null;
    }
  }

  private renderScrollState(): void {
    this.root.classList.toggle('scrolled', this.isScrolled);
    this.root.classList.toggle('header--on-hero', this.isOnHero);
  }

  private renderNavState(): void {
    if (this.nav) {
      this.nav.classList.toggle('open', this.isNavOpen);
      this.nav.setAttribute('aria-expanded', String(this.isNavOpen));
    }

    if (this.toggle) {
      this.toggle.classList.toggle('active', this.isNavOpen);
      this.toggle.setAttribute('aria-expanded', String(this.isNavOpen));
      this.toggle.setAttribute(
        'aria-label',
        this.isNavOpen ? 'Menü schließen' : 'Menü öffnen'
      );
    }
  }

  private open(): void {
    if (this.isNavOpen) return;

    if (this.closeTimer !== null) {
      window.clearTimeout(this.closeTimer);
      this.closeTimer = null;
    }

    this.scrollPosition = window.scrollY;
    this.isNavOpen = true;
    document.body.classList.add('nav-open');
    document.body.style.top = `-${this.scrollPosition}px`;
    this.renderNavState();
  }

  private close(): void {
    if (!this.isNavOpen) return;

    this.isNavOpen = false;
    this.renderNavState();

    // Hold the scroll lock until the drawer animation has fully run so
    // the page doesn't snap behind the closing menu.
    const restore = (): void => {
      document.body.classList.remove('nav-open');
      document.body.style.top = '';
      window.scrollTo({
        top: this.scrollPosition,
        left: 0,
        behavior: 'instant',
      });
      this.closeTimer = null;
    };

    if (prefersReducedMotion()) {
      restore();

      return;
    }

    this.closeTimer = window.setTimeout(restore, CLOSE_ANIMATION_MS);
  }

  private toggleNav(): void {
    if (this.isNavOpen) this.close();
    else this.open();
  }
}

export function setupSiteHeader(): void {
  mountAll('[data-component="site-header"]', (el) => new SiteHeader(el));
}
