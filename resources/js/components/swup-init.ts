/**
 * Swup 4 — AJAX page transitions.
 *
 * Replaces `<main id="main">` without a full reload. Re-initialises Alpine
 * on the new DOM subtree so x-data components in #main come alive, and
 * resets the Umami tracker on each page view.
 */

import Swup from 'swup';
import Alpine from 'alpinejs';
import { initUmamiKit } from '@/utils/umami-kit';

export function initSwup(): Swup {
  const swup = new Swup({
    containers: ['#main'],
    native: true,
    cache: true,
    linkSelector:
      'a[href]:not([data-no-swup]):not([download]):not([target="_blank"])',
  });

  swup.hooks.on('content:replace', () => {
    const main = document.getElementById('main');

    if (main) {
      Alpine.initTree(main);
    }
  });

  swup.hooks.on('page:view', () => {
    window.umamiTracker?.destroy();
    initUmamiKit();
  });

  return swup;
}
