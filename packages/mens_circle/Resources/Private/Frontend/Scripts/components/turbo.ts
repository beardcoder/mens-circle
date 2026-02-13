/**
 * Hotwire Turbo integration
 * Provides seamless page navigation and form submissions
 */

import * as Turbo from '@hotwired/turbo';

Turbo.start();

/**
 * Re-initialize components after Turbo page renders.
 * Turbo replaces the <body> on navigation, so any DOM-bound
 * setup must run again.
 */
export function onTurboLoad(callback: () => void): void {
  document.addEventListener('turbo:load', callback);
}

/**
 * Hook that runs before Turbo submits a form.
 * Useful for adding loading indicators.
 */
export function onTurboSubmitStart(callback: (event: Event) => void): void {
  document.addEventListener('turbo:submit-start', callback);
}

/**
 * Hook that runs after Turbo finishes a form submission.
 * Useful for removing loading indicators.
 */
export function onTurboSubmitEnd(callback: (event: Event) => void): void {
  document.addEventListener('turbo:submit-end', callback);
}
