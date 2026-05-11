/**
 * Site Header Alpine Component
 * Manages mobile navigation and scroll-based header state.
 */

export function siteHeader() {
  return {
    isNavOpen: false,
    isScrolled: false,
    isOnHero: false,
    scrollPosition: 0,
    _cleanup: [] as Array<() => void>,

    init() {
      const scrollThreshold = 50;
      const heroEl = document.querySelector<HTMLElement>('.hero');

      document.body.classList.toggle('has-hero', !!heroEl);
      document.body.classList.toggle('no-hero', !heroEl);

      const updateScroll = (): void => {
        this.isScrolled = window.scrollY > scrollThreshold || !heroEl;
      };

      updateScroll();
      window.addEventListener('scroll', updateScroll, { passive: true });
      this._cleanup.push(() =>
        window.removeEventListener('scroll', updateScroll)
      );

      if (heroEl) {
        const observer = new IntersectionObserver(
          (entries) => {
            entries.forEach((entry) => {
              this.isOnHero =
                entry.isIntersecting && entry.intersectionRatio > 0.15;
            });
          },
          { threshold: [0, 0.15, 0.35, 0.5], rootMargin: '-10% 0px 0px 0px' }
        );

        observer.observe(heroEl);
        this._cleanup.push(() => observer.disconnect());
      }

      const handleOutsideClick = (e: MouseEvent): void => {
        if (
          this.isNavOpen &&
          !(this as unknown as { $el: HTMLElement }).$el.contains(
            e.target as Node
          )
        ) {
          this.closeNav();
        }
      };

      const handleEscape = (e: KeyboardEvent): void => {
        if (e.key === 'Escape' && this.isNavOpen) {
          this.closeNav();
        }
      };

      document.addEventListener('click', handleOutsideClick);
      document.addEventListener('keydown', handleEscape);

      this._cleanup.push(() => {
        document.removeEventListener('click', handleOutsideClick);
        document.removeEventListener('keydown', handleEscape);
      });
    },

    openNav(): void {
      this.scrollPosition = window.scrollY;
      this.isNavOpen = true;
      document.body.classList.add('nav-open');
      document.body.style.top = `-${this.scrollPosition}px`;
    },

    closeNav(): void {
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

    closeNavImmediate(): void {
      if (!this.isNavOpen) return;

      this.isNavOpen = false;
      document.body.classList.remove('nav-open');
      document.body.style.top = '';
    },

    toggleNav(): void {
      if (this.isNavOpen) {
        this.closeNav();
      } else {
        this.openNav();
      }
    },

    destroy(): void {
      this._cleanup.forEach((fn) => fn());
      this._cleanup = [];
    },
  };
}

/**
 * Scroll progress bar Alpine component.
 *
 * Uses JS lerp instead of animation-timeline: scroll(root) to prevent the bar
 * from "jumping" when scrollHeight changes (lazy images, accordions, Swup swaps).
 */
export function scrollProgress() {
  return {
    _cleanup: [] as Array<() => void>,

    init() {
      const bar = (this as unknown as { $el: HTMLElement }).$el;
      const smoothing = 0.18;
      const root = document.documentElement;

      let target = 0;
      let current = 0;
      let raf = 0;
      let running = false;

      const computeTarget = (): void => {
        const max = root.scrollHeight - globalThis.innerHeight;

        target = max > 0 ? Math.min(1, Math.max(0, root.scrollTop / max)) : 0;
      };

      const tick = (): void => {
        const delta = target - current;

        if (Math.abs(delta) < 0.0005) {
          current = target;
          bar.style.transform = `scaleX(${current})`;
          running = false;

          return;
        }

        current += delta * smoothing;
        bar.style.transform = `scaleX(${current})`;
        raf = requestAnimationFrame(tick);
      };

      const schedule = (): void => {
        computeTarget();

        if (running) return;

        running = true;
        raf = requestAnimationFrame(tick);
      };

      computeTarget();
      current = target;
      bar.style.transform = `scaleX(${current})`;

      globalThis.addEventListener('scroll', schedule, { passive: true });
      globalThis.addEventListener('resize', schedule);

      const resizeObserver =
        typeof ResizeObserver === 'function'
          ? new ResizeObserver(schedule)
          : null;

      resizeObserver?.observe(root);
      if (document.body) resizeObserver?.observe(document.body);

      this._cleanup.push(() => {
        cancelAnimationFrame(raf);
        globalThis.removeEventListener('scroll', schedule);
        globalThis.removeEventListener('resize', schedule);
        resizeObserver?.disconnect();
      });
    },

    destroy(): void {
      this._cleanup.forEach((fn) => fn());
      this._cleanup = [];
    },
  };
}

/**
 * Scroll-to-top button Alpine component.
 */
export function scrollToTop() {
  return {
    isVisible: false,
    _cleanup: [] as Array<() => void>,

    init() {
      const threshold = 400;

      const update = (): void => {
        this.isVisible = window.scrollY > threshold;
      };

      update();
      window.addEventListener('scroll', update, { passive: true });
      this._cleanup.push(() => window.removeEventListener('scroll', update));
    },

    scrollUp(): void {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    destroy(): void {
      this._cleanup.forEach((fn) => fn());
      this._cleanup = [];
    },
  };
}
