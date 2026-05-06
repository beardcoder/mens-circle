/**
 * Accordion component
 *
 * Uses native <details>/<summary> for semantics and keyboard accessibility.
 * Open and close transitions are driven by the Web Animations API (WAAPI) so
 * that both directions are smooth — the CSS-only `interpolate-size` approach
 * only animates the open direction because the browser removes `[open]` before
 * any CSS transition can run the close.
 *
 * Single-open-per-group is still enforced via the `name` attribute: when an
 * item opens, siblings with the same name animate closed first.
 */

import { defineComponent } from '@beardcoder/stitch-js';

/** Duration for the expanding (opening) animation, in ms. */
const DURATION_OPEN = 400;

/** Duration for the collapsing (closing) animation, in ms. Slightly faster
 *  so the dismissal feels snappier than the reveal. */
const DURATION_CLOSE = 320;

/**
 * Matches `--ease-settle` in _variables.css.
 * Content arriving / leaving: deceleration curve.
 */
const EASING = 'cubic-bezier(0.22, 1, 0.36, 1)';

/**
 * Resolved once at module initialisation. Using `matchMedia(...).matches`
 * is cheap, but calling it inside every animation handler (open/close/sibling
 * close) adds redundant queries to the main thread. Cache it and let the
 * browser's change event update it for long-lived pages.
 */
let reducedMotion =
  typeof globalThis.matchMedia === 'function' &&
  globalThis.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (typeof globalThis.matchMedia === 'function') {
  globalThis
    .matchMedia('(prefers-reduced-motion: reduce)')
    .addEventListener('change', (e) => {
      reducedMotion = e.matches;
    });
}

interface NativeAccordionOptions {
  itemSelector: string;
}

export const nativeAccordion = defineComponent<NativeAccordionOptions>(
  { itemSelector: '.accordion-item' },
  (ctx) => {
    const accordions = Array.from(
      ctx.el.querySelectorAll<HTMLDetailsElement>(ctx.options.itemSelector)
    );

    if (accordions.length === 0) return;

    /**
     * Tracks the in-progress WAAPI animation for each item so rapid clicks
     * can cancel a running transition before starting the reversed one.
     */
    const animationMap = new WeakMap<HTMLDetailsElement, Animation>();

    const getContent = (
      details: HTMLDetailsElement
    ): HTMLElement | null =>
      details.querySelector<HTMLElement>('.accordion-item__content');

    /** Expand an accordion item with an animated height reveal. */
    const openItem = (details: HTMLDetailsElement): void => {
      const content = getContent(details);

      if (!content) {
        details.open = true;
        return;
      }

      if (reducedMotion) {
        details.open = true;
        return;
      }

      // Cancel any animation already running on this item.
      animationMap.get(details)?.cancel();

      // Pin the content at 0 height before revealing so there is no
      // single-frame flash of fully-expanded content.
      content.style.height = '0px';
      content.style.overflow = 'hidden';

      // Setting open adds the content to the layout; scrollHeight now
      // reports the full natural height even though we locked it to 0px.
      details.open = true;

      const targetHeight = content.scrollHeight;

      const anim = content.animate(
        [
          { height: '0px', opacity: '0' },
          { height: `${targetHeight}px`, opacity: '1' },
        ],
        { duration: DURATION_OPEN, easing: EASING }
      );

      animationMap.set(details, anim);

      anim.onfinish = () => {
        animationMap.delete(details);
        // Remove inline constraints so the content reflows naturally
        // (e.g. if the viewport resizes after the panel is open).
        content.style.removeProperty('height');
        content.style.removeProperty('overflow');
      };

      anim.oncancel = () => {
        animationMap.delete(details);
        content.style.removeProperty('height');
        content.style.removeProperty('overflow');
      };
    };

    /** Collapse an accordion item with an animated height shrink. */
    const closeItem = (details: HTMLDetailsElement): void => {
      const content = getContent(details);

      if (!content || !details.open) return;

      if (reducedMotion) {
        details.open = false;
        return;
      }

      // Cancel any animation already running on this item.
      animationMap.get(details)?.cancel();

      // Measure the actual rendered height at this moment (may differ from
      // scrollHeight if the item was mid-open when this was called).
      const startHeight = content.getBoundingClientRect().height;

      content.style.overflow = 'hidden';

      const anim = content.animate(
        [
          { height: `${startHeight}px`, opacity: '1' },
          { height: '0px', opacity: '0' },
        ],
        { duration: DURATION_CLOSE, easing: EASING }
      );

      animationMap.set(details, anim);

      anim.onfinish = () => {
        animationMap.delete(details);
        // Remove `open` only after the animation completes — the content
        // was visible (height > 0) throughout the transition.
        details.open = false;
        content.style.removeProperty('height');
        content.style.removeProperty('overflow');
      };

      anim.oncancel = () => {
        animationMap.delete(details);
        content.style.removeProperty('height');
        content.style.removeProperty('overflow');
      };
    };

    /** Animate-close all open siblings sharing the same `name` group. */
    const closeSiblings = (except: HTMLDetailsElement): void => {
      const name = except.getAttribute('name');
      if (!name) return;

      ctx.el
        .querySelectorAll<HTMLDetailsElement>(
          `[name="${name}"].accordion-item`
        )
        .forEach((sib) => {
          if (sib !== except && sib.open) closeItem(sib);
        });
    };

    const handleSummaryClick = (event: MouseEvent): void => {
      const summary = event.currentTarget as HTMLElement;
      const details = summary.closest<HTMLDetailsElement>('.accordion-item');
      if (!details) return;

      // We drive the toggle ourselves — prevent the browser default so we
      // can animate before changing the open state.
      event.preventDefault();

      if (details.open) {
        closeItem(details);
      } else {
        closeSiblings(details);
        openItem(details);
      }
    };

    accordions.forEach((details) => {
      const summary = details.querySelector<HTMLElement>('summary');
      summary?.addEventListener('click', handleSummaryClick);
    });

    ctx.onDestroy(() => {
      accordions.forEach((details) => {
        const summary = details.querySelector<HTMLElement>('summary');
        summary?.removeEventListener('click', handleSummaryClick);
        animationMap.get(details)?.cancel();
      });
    });
  }
);
