import { animate } from 'motion';

class MobileNavigation {
  private readonly navToggle: HTMLElement;
  private readonly nav: HTMLElement;
  private scrollPosition = 0;

  constructor(navToggle: HTMLElement, nav: HTMLElement) {
    this.navToggle = navToggle;
    this.nav = nav;
    this.bindEvents();
  }

  private async openNav(): Promise<void> {
    this.scrollPosition = window.pageYOffset;
    this.nav.classList.add('open');
    this.navToggle.classList.add('active');
    document.body.classList.add('nav-open');
    document.body.style.top = `-${this.scrollPosition}px`;
    this.navToggle.setAttribute('aria-expanded', 'true');
    this.navToggle.setAttribute('aria-label', 'Menu schliessen');

    // Animate navigation links with Motion.dev
    const navLinks = this.nav.querySelectorAll<HTMLElement>(
      '.nav__link, .nav__cta'
    );

    navLinks.forEach((link, index) => {
      const delay = index * 0.06;

      // Fade in with staggered timing
      animate(
        link,
        { opacity: [0, 1], transform: ['translateY(-12px)', 'translateY(0)'] },
        {
          duration: 0.4,
          delay,
          ease: [0.34, 1.56, 0.64, 1], // Gentle spring
        }
      );
    });
  }

  private async closeNav(): Promise<void> {
    // Animate out before removing classes
    const navLinks = this.nav.querySelectorAll<HTMLElement>(
      '.nav__link, .nav__cta'
    );

    navLinks.forEach((link) => {
      animate(
        link,
        { opacity: 0, transform: 'translateY(-8px)' },
        {
          duration: 0.2,
          ease: [0.32, 0.72, 0, 1], // Smooth deceleration
        }
      );
    });

    // Wait for animation to complete before cleanup
    setTimeout(() => {
      this.nav.classList.remove('open');
      this.navToggle.classList.remove('active');
      document.body.classList.remove('nav-open');
      document.body.style.top = '';
      window.scrollTo({
        top: this.scrollPosition,
        left: 0,
        behavior: 'instant',
      });
      this.navToggle.setAttribute('aria-expanded', 'false');
      this.navToggle.setAttribute('aria-label', 'Menu oeffnen');
    }, 200);
  }

  private toggleNav(): void {
    if (this.nav.classList.contains('open')) {
      this.closeNav();
    } else {
      this.openNav();
    }
  }

  private bindEvents(): void {
    this.navToggle.addEventListener('click', () => this.toggleNav());

    // Close nav when clicking on a link
    const navLinks = this.nav.querySelectorAll<HTMLAnchorElement>(
      '.nav__link, .nav__cta'
    );

    navLinks.forEach((link) => {
      link.addEventListener('click', () => this.closeNav());
    });

    // Close nav when clicking outside
    document.addEventListener('click', (e) => {
      const target = e.target as Node;

      if (
        !this.nav.contains(target) &&
        !this.navToggle.contains(target) &&
        this.nav.classList.contains('open')
      ) {
        this.closeNav();
      }
    });

    // Close nav on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.nav.classList.contains('open')) {
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
    console.error('Error initializing MobileNavigation:', error);
  }
}

/**
 * Header Scroll Effect Component
 * Adds a 'scrolled' class to the header when the page is scrolled
 */
export function initScrollHeader(): void {
  const header = document.getElementById('header');

  if (!header) return;

  const hasHero = Boolean(document.querySelector('.hero'));

  document.body.classList.toggle('has-hero', hasHero);
  document.body.classList.toggle('no-hero', !hasHero);

  const updateScrollState = (): void => {
    const currentScroll = window.pageYOffset;

    if (currentScroll > 50 || !hasHero) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  };

  updateScrollState();

  window.addEventListener('scroll', updateScrollState, { passive: true });

  if (hasHero) {
    const hero = document.querySelector<HTMLElement>('.hero');

    if (hero) {
      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting && entry.intersectionRatio > 0.15) {
              header.classList.add('header--on-hero');
            } else {
              header.classList.remove('header--on-hero');
            }
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
}
