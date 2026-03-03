import type { ComponentFactory } from '../utils/types';
import { enhance } from './enhance';

/** Registry of selector → factory pairs for auto-init. */
const registry: Array<{
  selector: string;
  factory: ComponentFactory;
}> = [];

/**
 * Register a component for automatic initialization.
 */
export function register<O>(
  selector: string,
  factory: ComponentFactory<O>
): void {
  registry.push({ selector, factory: factory as ComponentFactory });
}

/**
 * Initialize all registered components.
 * Safe to call multiple times — `enhance` is idempotent.
 */
export function init(root?: ParentNode): void {
  for (const { selector, factory } of registry) {
    enhance(selector, factory, root ? { root } : undefined);
  }
}

/**
 * Set up auto-initialization on DOMContentLoaded.
 */
export function autoInit(): void {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => init(), {
      once: true,
    });
  } else {
    init();
  }
}
