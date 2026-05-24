/**
 * Accordion — animates the `<details>` wrapper height with the Web
 * Animations API for cross-browser smoothness (iOS Safari included).
 *
 * Factory style. Each `<details.accordion-item>` mounts its own
 * instance via `mountAll`. Exclusive `name="…"` groups are coordinated
 * with a custom `accordion:about-to-open` document event so siblings
 * collapse animatedly instead of being snapped shut by the browser.
 */

import { prefersReducedMotion } from '@/utils/helpers';
import { createHost, mountAll, type Component } from '@/lib/host';

const DURATION_MS = 320;
const EASING = 'cubic-bezier(0.22, 1, 0.36, 1)';
const OPEN_EVENT = 'accordion:about-to-open';

interface AccordionOpenDetail {
  name: string;
  source: HTMLDetailsElement;
}

function createAccordion(root: HTMLElement): Component | null {
  if (!(root instanceof HTMLDetailsElement)) return null;

  const details = root;
  const host = createHost(root);
  const summary = host.query<HTMLElement>('summary');
  const body = host.query<HTMLElement>('.accordion-item__body');

  if (!summary || !body) return null;

  let animation: Animation | null = null;
  let isClosing = false;

  const resetInlineStyles = (): void => {
    details.style.removeProperty('height');
    details.style.removeProperty('overflow');
  };

  const finish = (open: boolean): void => {
    details.open = open;
    animation = null;
    isClosing = false;
    resetInlineStyles();
  };

  const dispatchOpenEvent = (): void => {
    document.dispatchEvent(
      new CustomEvent<AccordionOpenDetail>(OPEN_EVENT, {
        detail: { name: details.name, source: details },
      })
    );
  };

  const expand = (): void => {
    if (details.name) dispatchOpenEvent();

    const startHeight = `${details.offsetHeight}px`;

    details.style.height = startHeight;
    details.open = true;

    window.requestAnimationFrame(() => {
      const endHeight = `${summary.offsetHeight + body.offsetHeight}px`;

      animation?.cancel();
      isClosing = false;

      animation = details.animate(
        { height: [startHeight, endHeight] },
        { duration: DURATION_MS, easing: EASING }
      );

      animation.onfinish = () => finish(true);
    });
  };

  const shrink = (): void => {
    details.style.overflow = 'hidden';

    const startHeight = `${details.offsetHeight}px`;
    const endHeight = `${summary.offsetHeight}px`;

    animation?.cancel();
    isClosing = true;

    animation = details.animate(
      { height: [startHeight, endHeight] },
      { duration: DURATION_MS, easing: EASING }
    );

    animation.onfinish = () => finish(false);
    animation.oncancel = () => {
      isClosing = false;
    };
  };

  const toggleInstant = (): void => {
    if (details.open) {
      details.open = false;

      return;
    }

    if (details.name) dispatchOpenEvent();
    details.open = true;
  };

  host.on(summary, 'click', (event) => {
    event.preventDefault();

    if (prefersReducedMotion()) {
      toggleInstant();

      return;
    }

    details.style.overflow = 'hidden';

    if (isClosing || !details.open) expand();
    else shrink();
  });

  host.onDocument(OPEN_EVENT as keyof DocumentEventMap, (event) => {
    const detail = (event as CustomEvent<AccordionOpenDetail>).detail;

    if (
      detail.name &&
      detail.name === details.name &&
      detail.source !== details &&
      details.open
    ) {
      shrink();
    }
  });

  return {
    destroy(): void {
      animation?.cancel();
      resetInlineStyles();
      host.destroy();
    },
  };
}

export function setupAccordion(): void {
  mountAll('details.accordion-item', createAccordion);
}
