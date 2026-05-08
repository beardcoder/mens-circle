/**
 * Alpine.js Navigation Component
 * Mobile navigation with smooth open/close transitions
 */

export function mobileNav() {
  return {
    isOpen: false,
    scrollPosition: 0,

    open(): void {
      this.scrollPosition = window.scrollY;
      this.isOpen = true;
      document.body.classList.add('nav-open');
      document.body.style.top = `-${this.scrollPosition}px`;
    },

    close(options: { restoreScroll?: boolean } = {}): void {
      if (!this.isOpen) return;

      this.isOpen = false;
      document.body.classList.remove('nav-open');
      document.body.style.top = '';

      if (options.restoreScroll ?? true) {
        window.scrollTo({
          top: this.scrollPosition,
          left: 0,
          behavior: 'instant' as ScrollBehavior,
        });
      }
    },

    toggle(): void {
      if (this.isOpen) {
        this.close();
      } else {
        this.open();
      }
    },

    handleLinkClick(): void {
      if (this.isOpen) {
        this.close({ restoreScroll: false });
      }
    },
  };
}
