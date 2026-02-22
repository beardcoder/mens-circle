/**
 * Accordion composable
 * Uses native <details> behavior and adds single-open fallback by group.
 */

function closeSiblingAccordions(
  current: HTMLDetailsElement,
  groupName: string
): void {
  document
    .querySelectorAll<HTMLDetailsElement>(
      `.accordion-item[name="${groupName}"], .faq-item[name="${groupName}"]`
    )
    .forEach((details) => {
      if (details !== current) {
        details.open = false;
      }
    });
}

export function useAccordions(): void {
  const accordions = document.querySelectorAll<HTMLDetailsElement>(
    '.accordion-item, .faq-item'
  );

  if (accordions.length === 0) {
    return;
  }

  accordions.forEach((details) => {
    details.addEventListener('toggle', () => {
      if (!details.open) {
        return;
      }

      const groupName = details.getAttribute('name');

      if (!groupName) {
        return;
      }

      closeSiblingAccordions(details, groupName);
    });
  });
}
