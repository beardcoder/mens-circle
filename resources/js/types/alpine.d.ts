/**
 * Alpine.js magic property types for component `this` context.
 */

/** Magic properties injected by Alpine into data component context. */
export interface AlpineMagics {
  $el: HTMLElement;
  $refs: Record<string, HTMLElement>;
  $nextTick(callback?: () => void): Promise<void>;
  $dispatch(event: string, data?: Record<string, unknown>): void;
  $watch(
    property: string,
    callback: (value: unknown, oldValue: unknown) => void
  ): void;
}

/**
 * Use as a `this` parameter type in Alpine data component methods
 * to make Alpine magic properties available to TypeScript.
 */
export type AlpineThis = AlpineMagics & Record<string, unknown>;
