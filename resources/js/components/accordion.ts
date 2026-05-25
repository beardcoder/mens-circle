/**
 * Accordion — animates the `<details>` wrapper height with the Web
 * Animations API for cross-browser smoothness (iOS Safari included).
 *
 * Each `<details.accordion-item>` is mounted as a Lume component.
 * Exclusive `name="…"` groups are coordinated with a local Lume event
 * so siblings collapse animatedly instead of snapping shut.
 */

import { prefersReducedMotion } from '@/utils/helpers';
import { defineComponent } from '@beardcoder/lume';

const DURATION_MS = 320;
const EASING = 'cubic-bezier(0.22, 1, 0.36, 1)';
const OPEN_EVENT = 'accordion:about-to-open';

interface AccordionOpenDetail {
  name: string;
  source: HTMLDetailsElement;
}

export default defineComponent(({ root, on, emit, listen, cleanup }) => {
  if (!(root instanceof HTMLDetailsElement)) return {};

  const details = root;
  const summary = root.querySelector<HTMLElement>('summary');
  const body = root.querySelector<HTMLElement>('.accordion-item__body');

  if (!summary || !body) return {};

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
    emit(OPEN_EVENT, { name: details.name, source: details });
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

  on(summary, 'click', (event) => {
    event.preventDefault();

    if (prefersReducedMotion()) {
      toggleInstant();

      return;
    }

    details.style.overflow = 'hidden';

    if (isClosing || !details.open) expand();
    else shrink();
  });

  listen(OPEN_EVENT, (detail) => {
    const { name, source } = detail as AccordionOpenDetail;

    if (name && name === details.name && source !== details && details.open) {
      shrink();
    }
  });

  cleanup(() => {
    animation?.cancel();
    resetInlineStyles();
  });

  return {};
});
