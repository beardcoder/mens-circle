/**
 * Swup 4 — AJAX page transitions for the public site.
 *
 * - Replaces the `<main id="main">` container without a full reload.
 * - Re-runs stitch component initialization on the new DOM (idempotent).
 * - Resets the Umami tracker on every page view so per-page metrics
 *   (scroll depth, time on page, etc.) start fresh.
 * - Uses native View Transitions where supported via `native: true`.
 */

import Swup from 'swup';
import { init as initStitch, destroyAll } from '@beardcoder/stitch-js';
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
    // The body-scoped scroll-animations component observes elements inside
    // #main; tear it down before re-init so the new content gets fresh
    // IntersectionObservers.
    destroyAll('body');
    initStitch();
  });

  swup.hooks.on('page:view', () => {
    window.umamiTracker?.destroy();
    initUmamiKit();
  });

  return swup;
}
