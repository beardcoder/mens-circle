/**
 * Scroll-to-Top Button
 *
 * Toggles `.scroll-to-top--visible` based on scroll position; clicking
 * the button smooth-scrolls back to the top. Factory style — no class.
 */

import { createHost, mountAll, type Component } from '@/lib/host';

const VISIBILITY_THRESHOLD_PX = 400;
const VISIBLE_CLASS = 'scroll-to-top--visible';

function createScrollToTop(root: HTMLElement): Component | null {
  if (!(root instanceof HTMLButtonElement)) return null;

  const host = createHost(root);

  const update = (): void => {
    root.classList.toggle(
      VISIBLE_CLASS,
      window.scrollY > VISIBILITY_THRESHOLD_PX
    );
  };

  root.style.removeProperty('display');

  host.on(root, 'click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  host.onWindow('scroll', update, { passive: true });
  update();

  return { destroy: host.destroy };
}

export function setupScrollToTop(): void {
  mountAll('.scroll-to-top', createScrollToTop);
}
