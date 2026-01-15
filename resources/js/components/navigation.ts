/**
 * Navigation Component - Modern Implementation
 * Handles mobile navigation with smooth animations using Motion One
 */

import { animate } from 'motion';

interface NavigationState {
  isOpen: boolean;
  scrollPosition: number;
}

class MobileNavigation {
  private readonly navToggle: HTMLElement;
  private readonly nav: HTMLElement;
  private state: NavigationState = {
    isOpen: false,
    scrollPosition: 0,
  };

  constructor(navToggle: HTMLElement, nav: HTMLElement) {
    this.navToggle = navToggle;
    this.nav = nav;
    this.bindEvents();
  }

  private async openNav(): Promise<void> {
    this.state.scrollPosition = window.scrollY;
    this.state.isOpen = true;

    this.nav.classList.add('open');
    this.navToggle.classList.add('active');
    document.body.classList.add('nav-open');
    document.body.style.top = `-${this.state.scrollPosition}px`;

    this.updateAriaAttributes(true);
    await this.animateNavLinks('in');
  }

  private async closeNav(): Promise<void> {
    this.state.isOpen = false;
    await this.animateNavLinks('out');

    setTimeout(() => {
      this.nav.classList.remove('open');
      this.navToggle.classList.remove('active');
      document.body.classList.remove('nav-open');
      document.body.style.top = '';
      
      window.scrollTo({
        top: this.state.scrollPosition,
        left: 0,
        behavior: 'instant',
      });

      this.updateAriaAttributes(false);
    }, 200);
  }

  private async animateNavLinks(direction: 'in' | 'out'): Promise<void> {
    const navLinks = Array.from(
      this.nav.querySelectorAll<HTMLElement>('.nav__link, .nav__cta')
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
  }

  private updateAriaAttributes(isOpen: boolean): void {
    this.navToggle.setAttribute('aria-expanded', String(isOpen));
    this.navToggle.setAttribute(
      'aria-label',
      isOpen ? 'Menü schließen' : 'Menü öffnen'
    );
  }

  private toggleNav(): void {
    this.state.isOpen ? this.closeNav() : this.openNav();
  }

  private bindEvents(): void {
    this.navToggle.addEventListener('click', () => this.toggleNav());

    const navLinks = this.nav.querySelectorAll<HTMLAnchorElement>(
      '.nav__link, .nav__cta'
    );
    navLinks.forEach((link) => {
      link.addEventListener('click', () => this.closeNav());
    });

    document.addEventListener('click', (e) => {
      if (
        this.state.isOpen &&
        !this.nav.contains(e.target as Node) &&
        !this.navToggle.contains(e.target as Node)
      ) {
        this.closeNav();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.state.isOpen) {
        this.closeNav();
      }
    });
  }
}

export function initNavigation(): void {
  const navToggle = document.getElementById('navToggle');
  const nav = document.getElementById('nav');

  if (!navToggle || !nav) return;

  try {
    new MobileNavigation(navToggle, nav);
  } catch (error) {
    console.error('Navigation initialization failed:', error);
  }
}

/**
 * Header Scroll Effect
 * Updates header appearance based on scroll position and hero presence
 */
export function initScrollHeader(): void {
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
    initHeroObserver(header);
  }
}

function initHeroObserver(header: HTMLElement): void {
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

