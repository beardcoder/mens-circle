/**
 * Accordion Animation — cross-browser, iOS-safe
 *
 * The CSS-only approach with `::details-content` + `interpolate-size:
 * allow-keywords` only ships in Chrome 131 / Safari 18.2. On older
 * Safari (iOS in particular) and every Firefox, panels snap.
 *
 * This module animates the `<details>` element itself with the Web
 * Animations API. Animating the wrapper (rather than the inner body)
 * sidesteps WebKit's atomic open/close behaviour that otherwise hides
 * the body children mid-animation, producing the iOS snap.
 *
 * The state machine is the well-known Jhey Tompkins / web.dev pattern:
 *
 *   - intercept the `<summary>` click
 *   - on expand: set `open=true`, animate height from summary-only to
 *     summary + body
 *   - on collapse: leave `open=true`, animate height back down, then
 *     flip `open=false` in the `onfinish` callback
 *
 * `name="…"` exclusive groups are coordinated with a custom
 * `accordion:about-to-open` event so siblings collapse with the same
 * animation instead of being snapped shut by the browser.
 *
 * Honours `prefers-reduced-motion`: snaps instantly without the
 * animation overhead.
 */

import { prefersReducedMotion } from '@/utils/helpers';
import { mountAll, ReactiveHost } from '@/lib/reactive-host';

const DURATION_MS = 320;
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
  private animation: Animation | null = null;
  private isExpanding = false;
  private isClosing = false;

  protected setup(): void {
    if (!(this.root instanceof HTMLDetailsElement)) return;

    this.details = this.root;

    const summary = this.query<HTMLElement>('summary');
    const body = this.query<HTMLElement>('.accordion-item__body');

    if (!summary || !body) return;

    this.summary = summary;
    this.body = body;

    this.on(this.summary, 'click', (event) => this.onSummaryClick(event));

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
        this.shrink();
      }
    });
  }

  protected teardown(): void {
    this.animation?.cancel();
    this.resetInlineStyles();
  }

  private onSummaryClick(event: MouseEvent): void {
    event.preventDefault();

    if (prefersReducedMotion()) {
      this.toggleInstant();

      return;
    }

    // Clip overflow during the animation so the body content never
    // overflows the height we're tweening.
    this.details.style.overflow = 'hidden';

    if (this.isClosing || !this.details.open) {
      this.expand();
    } else {
      this.shrink();
    }
  }

  private toggleInstant(): void {
    if (this.details.open) {
      this.details.open = false;

      return;
    }

    if (this.details.name) {
      this.dispatchOpenEvent();
    }

    this.details.open = true;
  }

  private dispatchOpenEvent(): void {
    document.dispatchEvent(
      new CustomEvent<AccordionOpenDetail>(OPEN_EVENT, {
        detail: { name: this.details.name, source: this.details },
      })
    );
  }

  /**
   * Expand: open natively, then animate the details element's height
   * from collapsed (summary only) up to its full expanded height.
   */
  private expand(): void {
    if (this.details.name) {
      this.dispatchOpenEvent();
    }

    // Capture current rendered height so the animation starts from
    // wherever the user clicked — including mid-shrink.
    const startHeight = `${this.details.offsetHeight}px`;

    this.details.open = true;

    // Wait one frame so the body has laid out and `offsetHeight`
    // reflects the natural expanded size.
    window.requestAnimationFrame(() => {
      const endHeight = `${this.summary.offsetHeight + this.body.offsetHeight}px`;

      this.animation?.cancel();
      this.isExpanding = true;
      this.isClosing = false;

      this.animation = this.details.animate(
        { height: [startHeight, endHeight] },
        { duration: DURATION_MS, easing: EASING }
      );

      this.animation.onfinish = () => this.finish(true);
      this.animation.oncancel = () => {
        this.isExpanding = false;
      };
    });
  }

  /**
   * Collapse: animate height down to summary, then flip open=false in
   * the finish callback so the body stays visible throughout the run.
   */
  private shrink(): void {
    this.details.style.overflow = 'hidden';

    const startHeight = `${this.details.offsetHeight}px`;
    const endHeight = `${this.summary.offsetHeight}px`;

    this.animation?.cancel();
    this.isClosing = true;
    this.isExpanding = false;

    this.animation = this.details.animate(
      { height: [startHeight, endHeight] },
      { duration: DURATION_MS, easing: EASING }
    );

    this.animation.onfinish = () => this.finish(false);
    this.animation.oncancel = () => {
      this.isClosing = false;
    };
  }

  private finish(open: boolean): void {
    this.details.open = open;
    this.animation = null;
    this.isExpanding = false;
    this.isClosing = false;
    this.resetInlineStyles();
  }

  private resetInlineStyles(): void {
    this.details.style.removeProperty('height');
    this.details.style.removeProperty('overflow');
  }
}

export function setupAccordion(): void {
  mountAll('details.accordion-item', (el) => new AccordionItem(el));
}
