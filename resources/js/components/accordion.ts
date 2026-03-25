/**
 * Accordion component
 * Uses native <details> with anime.js for smooth height animation
 * and single-open-per-group via stitch-js
 */

import { defineComponent } from '@stitch';
import { animate } from 'animejs';

interface NativeAccordionOptions {
  itemSelector: string;
}

function prefersReducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * Animate the content panel open or closed using anime.js.
 * Measures the natural height, then smoothly animates from 0 → measured
 * (or the reverse) with opacity for a polished feel.
 */
function animatePanel(
  details: HTMLDetailsElement,
  content: HTMLElement,
  isOpening: boolean
): globalThis.Animation | null {
  if (prefersReducedMotion()) return null;

  // Measure the target height
  content.style.overflow = 'hidden';

  if (isOpening) {
    // Temporarily make content visible so we can measure
    content.style.display = 'block';
    content.style.blockSize = 'auto';

    const measured = content.offsetHeight;

    content.style.blockSize = '0px';
    content.style.opacity = '0';

    animate(content, {
      blockSize: ['0px', `${measured}px`],
      opacity: [0, 1],
      duration: 400,
      ease: 'outQuint',
      onComplete: () => {
        content.style.blockSize = 'auto';
        content.style.overflow = '';
        content.style.opacity = '';
      },
    });
  } else {
    const current = content.offsetHeight;

    content.style.blockSize = `${current}px`;

    animate(content, {
      blockSize: [`${current}px`, '0px'],
      opacity: [1, 0],
      duration: 300,
      ease: 'inOutCubic',
      onComplete: () => {
        details.open = false;
        content.style.blockSize = '';
        content.style.overflow = '';
        content.style.opacity = '';
      },
    });
  }

  return null;
}

/**
 * Native details accordion component with anime.js height animation
 * Attach to a wrapper containing .accordion-item or .faq-item <details> elements
 * Enforces single-open-per-group and smooth animated open/close
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

    const useAnimation = !prefersReducedMotion();

    // Track currently closing details to prevent re-triggering
    const closingSet = new WeakSet<HTMLDetailsElement>();

    const handleClick = (event: Event): void => {
      const summary = (event.target as HTMLElement).closest('summary');

      if (!summary) return;

      const details = summary.closest('details') as HTMLDetailsElement | null;

      if (!details) return;

      // Find the content panel (the element after <summary>)
      const content = details.querySelector<HTMLElement>(
        '.accordion-item__content, .faq-item__answer'
      );

      if (!content || !useAnimation) return;

      if (details.open) {
        // Closing — prevent native toggle, animate out, then close
        event.preventDefault();

        if (closingSet.has(details)) return;

        closingSet.add(details);

        animate(content, {
          blockSize: [`${content.offsetHeight}px`, '0px'],
          opacity: [1, 0],
          duration: 300,
          ease: 'inOutCubic',
          onComplete: () => {
            details.open = false;
            content.style.blockSize = '';
            content.style.overflow = '';
            content.style.opacity = '';
            closingSet.delete(details);
          },
        });

        content.style.overflow = 'hidden';
      } else {
        // Opening — let native toggle happen, then animate in
        // Close siblings first (single-open behaviour)
        const groupName = details.getAttribute('name');

        if (groupName) {
          ctx.el
            .querySelectorAll<HTMLDetailsElement>(
              `.accordion-item[name="${groupName}"], .faq-item[name="${groupName}"]`
            )
            .forEach((sibling) => {
              if (sibling !== details && sibling.open) {
                const siblingContent = sibling.querySelector<HTMLElement>(
                  '.accordion-item__content, .faq-item__answer'
                );

                if (siblingContent) {
                  closingSet.add(sibling);

                  const h = siblingContent.offsetHeight;

                  siblingContent.style.overflow = 'hidden';

                  animate(siblingContent, {
                    blockSize: [`${h}px`, '0px'],
                    opacity: [1, 0],
                    duration: 280,
                    ease: 'inOutCubic',
                    onComplete: () => {
                      sibling.open = false;
                      siblingContent.style.blockSize = '';
                      siblingContent.style.overflow = '';
                      siblingContent.style.opacity = '';
                      closingSet.delete(sibling);
                    },
                  });
                } else {
                  sibling.open = false;
                }
              }
            });
        }

        // Animate the opening panel after the native toggle fires
        requestAnimationFrame(() => {
          animatePanel(details, content, true);
        });
      }
    };

    // Use click on container (summary click bubbles up)
    ctx.el.addEventListener('click', handleClick);

    ctx.onDestroy(() => {
      ctx.el.removeEventListener('click', handleClick);
    });
  }
);
