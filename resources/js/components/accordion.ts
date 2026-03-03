/**
 * Accordion component
 * Uses native <details> behavior with single-open-per-group via stitch-js
 */

import { defineComponent } from '@stitch';

interface NativeAccordionOptions {
  itemSelector: string;
}

/**
 * Native details accordion component
 * Attach to a wrapper containing .accordion-item or .faq-item <details> elements
 * Enforces single-open-per-group using the name attribute
 */
export const nativeAccordion = defineComponent<NativeAccordionOptions>(
  {
    itemSelector: '.accordion-item, .faq-item',
  },
  (ctx) => {
    const { options: o } = ctx;
    const accordions = ctx.el.querySelectorAll<HTMLDetailsElement>(
      o.itemSelector
    );

    if (accordions.length === 0) return;

    const handleToggle = (event: Event): void => {
      const details = event.target as HTMLDetailsElement;

      if (!details.open) return;

      const groupName = details.getAttribute('name');

      if (!groupName) return;

      ctx.el
        .querySelectorAll<HTMLDetailsElement>(
          `.accordion-item[name="${groupName}"], .faq-item[name="${groupName}"]`
        )
        .forEach((sibling) => {
          if (sibling !== details) {
            sibling.open = false;
          }
        });
    };

    accordions.forEach((details) => {
      details.addEventListener('toggle', handleToggle);
    });

    ctx.onDestroy(() => {
      accordions.forEach((details) => {
        details.removeEventListener('toggle', handleToggle);
      });
    });
  }
);
