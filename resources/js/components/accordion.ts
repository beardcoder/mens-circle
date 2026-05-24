/**
 * Accordion Animation
 *
 * The CSS-only approach using `::details-content` + `interpolate-size:
 * allow-keywords` is great when supported but only works in Chrome 131+
 * and Safari 18.2+, which leaves a lot of users on older Safari and
 * every Firefox snapping the panels open with no transition at all.
 *
 * This module fixes that by intercepting `summary` clicks on every
 * `details.accordion-item` and animating the body's height + opacity
 * with the Web Animations API — supported in every browser that ships
 * `<details>`. `name="…"` exclusive groups are handled via a custom
 * `accordion:about-to-open` event so opening one sibling smoothly
 * collapses the previous one.
 *
 * Honours `prefers-reduced-motion`: if the user has requested less
 * motion the click falls back to native instant toggling.
 */

import { prefersReducedMotion } from '@/utils/helpers';
import { mountAll, ReactiveHost } from '@/lib/reactive-host';

const DURATION_MS = 280;
const EASING = 'cubic-bezier(0.22, 1, 0.36, 1)';
const OPEN_EVENT = 'accordion:about-to-open';

interface AccordionOpenDetail {
  name: string;
  source: HTMLDetailsElement;
}

class AccordionItem extends ReactiveHost {
  private details!: HTMLDetailsElement;
  private summary!: HTMLElement;
  private body!: HTMLElement;
  private currentAnim: Animation | null = null;

  protected setup(): void {
    if (!(this.root instanceof HTMLDetailsElement)) return;

    this.details = this.root;

    const summary = this.query<HTMLElement>('summary');
    const body = this.query<HTMLElement>('.accordion-item__body');

    if (!summary || !body) return;

    this.summary = summary;
    this.body = body;

    this.on(this.summary, 'click', (event) => {
      event.preventDefault();

      if (prefersReducedMotion()) {
        this.toggleInstant();

        return;
      }

      if (this.details.open) {
        this.collapse();
      } else {
        this.requestOpen();
      }
    });

    // Listen for sibling-opening events from the same exclusive group so
    // we can collapse this panel smoothly instead of letting the browser
    // snap it closed.
    this.onDocument(OPEN_EVENT as keyof DocumentEventMap, (event) => {
      const detail = (event as CustomEvent<AccordionOpenDetail>).detail;

      if (
        detail.name &&
        detail.name === this.details.name &&
        detail.source !== this.details &&
        this.details.open
      ) {
        this.collapse();
      }
    });
  }

  protected teardown(): void {
    this.currentAnim?.cancel();
    this.resetInlineStyles();
  }

  private toggleInstant(): void {
    if (this.details.open) {
      this.details.open = false;

      return;
    }

    // Browser handles closing exclusive-group siblings natively when we
    // simply flip `open` to true.
    this.details.open = true;
  }

  private requestOpen(): void {
    const groupName = this.details.name;

    if (groupName) {
      document.dispatchEvent(
        new CustomEvent<AccordionOpenDetail>(OPEN_EVENT, {
          detail: { name: groupName, source: this.details },
        })
      );
    }

    this.expand();
  }

  private expand(): void {
    this.currentAnim?.cancel();
    this.details.open = true;

    // Force layout so scrollHeight reflects the final natural height.
    const endHeight = this.body.scrollHeight;

    this.body.style.overflow = 'hidden';

    this.currentAnim = this.body.animate(
      [
        { height: '0px', opacity: 0 },
        { height: `${endHeight}px`, opacity: 1 },
      ],
      { duration: DURATION_MS, easing: EASING }
    );

    this.currentAnim.onfinish = () => {
      this.resetInlineStyles();
      this.currentAnim = null;
    };
  }

  private collapse(): void {
    this.currentAnim?.cancel();

    const startHeight = this.body.scrollHeight;

    this.body.style.overflow = 'hidden';

    this.currentAnim = this.body.animate(
      [
        { height: `${startHeight}px`, opacity: 1 },
        { height: '0px', opacity: 0 },
      ],
      { duration: DURATION_MS, easing: EASING }
    );

    this.currentAnim.onfinish = () => {
      this.details.open = false;
      this.resetInlineStyles();
      this.currentAnim = null;
    };
  }

  private resetInlineStyles(): void {
    this.body.style.removeProperty('overflow');
    this.body.style.removeProperty('height');
    this.body.style.removeProperty('opacity');
  }
}

export function setupAccordion(): void {
  mountAll('details.accordion-item', (el) => new AccordionItem(el));
}
