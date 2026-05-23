/**
 * Scroll-to-Top Button
 *
 * Plain DOM enhancement — no framework. Looks for `<button class="scroll-to-top">`
 * elements in the document, drives a `.scroll-to-top--visible` class based on
 * scroll position, and binds the click to a smooth scroll to the top.
 */

const VISIBILITY_THRESHOLD_PX = 400;
const VISIBLE_CLASS = 'scroll-to-top--visible';

export function setupScrollToTop(): () => void {
  const buttons = Array.from(
    document.querySelectorAll<HTMLButtonElement>('.scroll-to-top')
  );

  if (buttons.length === 0) return () => {};

  const controller = new AbortController();
  const { signal } = controller;

  const update = (): void => {
    const visible = window.scrollY > VISIBILITY_THRESHOLD_PX;

    for (const button of buttons) {
      button.classList.toggle(VISIBLE_CLASS, visible);
    }
  };

  for (const button of buttons) {
    button.style.removeProperty('display');
    button.addEventListener(
      'click',
      () => window.scrollTo({ top: 0, behavior: 'smooth' }),
      { signal }
    );
  }

  window.addEventListener('scroll', update, { passive: true, signal });
  update();

  return () => controller.abort();
}
