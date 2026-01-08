/**
 * FAQ Accordion
 * Uses native <details>/<summary> elements with CSS animations
 * This function provides optional enhancements like analytics tracking
 */
export function initFAQ(): void {
  const faqItems = document.querySelectorAll<HTMLDetailsElement>('.faq-item');

  if (faqItems.length === 0) {
    return;
  }

  // Optional: Add analytics or other enhancements here
  faqItems.forEach((details) => {
    details.addEventListener('toggle', () => {
      // You could track analytics here
      if (details.open) {
        console.debug('FAQ item opened:', details.id || 'unnamed');
      }
    });
  });
}
