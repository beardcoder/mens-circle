/**
 * @beardcoder/stitch-js v1.0.1
 * A tiny, composable progressive enhancement framework for the browser.
 * Vendored from: https://github.com/beardcoder/stitch-js
 * License: MIT
 */

// Core
export { enhance, destroyAll } from './core/enhance';
export { register, init, autoInit } from './core/auto';
export { defineComponent } from './core/component';
export { createStore, computed, effect } from './core/store';

// Utilities
export { queryAll, setAria, uid } from './utils/dom';

// Types
export type {
  CleanupFn,
  ComponentInstance,
  ComponentFactory,
  EnhanceOptions,
} from './utils/types';

export type { ComponentContext, SetupFn } from './core/component';
export type { Store, Computed, Listener } from './core/store';
