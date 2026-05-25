/**
 * Scroll-to-Top Button
 *
 * Toggles `.scroll-to-top--visible` based on scroll position; clicking
 * the button smooth-scrolls back to the top. Factory style — no class.
 */

import { defineComponent } from '@beardcoder/lume';

const VISIBILITY_THRESHOLD_PX = 400;
const VISIBLE_CLASS = 'scroll-to-top--visible';

export default defineComponent(({ root, on }) => {
  const update = (): void => {
    root.classList.toggle(
      VISIBLE_CLASS,
      window.scrollY > VISIBILITY_THRESHOLD_PX
    );
  };

  root.style.removeProperty('display');

  on(root, 'click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  on(window, 'scroll', update, { passive: true });
  update();

  return {};
});
