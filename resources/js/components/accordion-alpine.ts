/**
 * Alpine.js Accordion Component
 * Single-open accordion using Alpine Collapse plugin
 */

export function accordion() {
  return {
    activeItem: null as string | null,

    toggle(itemId: string): void {
      this.activeItem = this.activeItem === itemId ? null : itemId;
    },

    isOpen(itemId: string): boolean {
      return this.activeItem === itemId;
    },
  };
}
