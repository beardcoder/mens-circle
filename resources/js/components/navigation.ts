class MobileNavigation {
  private readonly navToggle: HTMLElement;
  private readonly nav: HTMLElement;
  private scrollPosition = 0;

  constructor(navToggle: HTMLElement, nav: HTMLElement) {
    this.navToggle = navToggle;
    this.nav = nav;
    this.bindEvents();
  }

  private openNav(): void {
    this.scrollPosition = window.pageYOffset;
    this.nav.classList.add('open');
    this.navToggle.classList.add('active');
    document.body.classList.add('nav-open');
    document.body.style.top = `-${this.scrollPosition}px`;
    this.navToggle.setAttribute('aria-expanded', 'true');
    this.navToggle.setAttribute('aria-label', 'Menu schliessen');
  }

  private closeNav(): void {
    this.nav.classList.remove('open');
    this.navToggle.classList.remove('active');
    document.body.classList.remove('nav-open');
    document.body.style.top = '';
    window.scrollTo({ top: this.scrollPosition, left: 0, behavior: 'instant' });
    this.navToggle.setAttribute('aria-expanded', 'false');
    this.navToggle.setAttribute('aria-label', 'Menu oeffnen');
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

  new MobileNavigation(navToggle, nav);
}

/**
 * Header Scroll Effect Component
 * Adds a 'scrolled' class to the header when the page is scrolled
 */
export function initScrollHeader(): void {
  const header = document.getElementById('header');

  if (!header) return;

  window.addEventListener(
    'scroll',
    () => {
      const currentScroll = window.pageYOffset;

      if (currentScroll > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    },
    { passive: true }
  );
}
