import type { CleanupFn } from '../utils/types';

/** Listener callback for store subscriptions. */
export type Listener<T> = (value: T, prev: T) => void;

/** A minimal reactive store. */
export interface Store<T> {
  get(): T;
  set(value: T): void;
  update(fn: (current: T) => T): void;
  subscribe(listener: Listener<T>): CleanupFn;
}

export function createStore<T>(initial: T): Store<T> {
  let value = initial;
  const listeners = new Set<Listener<T>>();

  function notify(prev: T): void {
    for (const fn of listeners) {
      fn(value, prev);
    }
  }

  return {
    get() {
      return value;
    },

    set(next: T) {
      const prev = value;

      if (Object.is(prev, next)) return;

      value = next;
      notify(prev);
    },

    update(fn: (current: T) => T) {
      const prev = value;
      const next = fn(prev);

      if (Object.is(prev, next)) return;

      value = next;
      notify(prev);
    },

    subscribe(listener: Listener<T>): CleanupFn {
      listeners.add(listener);

      return () => {
        listeners.delete(listener);
      };
    },
  };
}

/** A read-only derived store. */
export interface Computed<T> {
  get(): T;
  subscribe(listener: Listener<T>): CleanupFn;
  dispose(): void;
}

export function computed<S extends Store<unknown>[], T>(
  sources: [...S],
  derive: (
    ...values: {
      [K in keyof S]: S[K] extends Store<infer V> ? V : never;
    }
  ) => T
): Computed<T> {
  const listeners = new Set<Listener<T>>();

  function getValues() {
    return sources.map((s) => s.get()) as {
      [K in keyof S]: S[K] extends Store<infer V> ? V : never;
    };
  }

  let value = derive(...getValues());

  function recompute(): void {
    const prev = value;

    value = derive(...getValues());

    if (Object.is(prev, value)) return;

    for (const fn of listeners) {
      fn(value, prev);
    }
  }

  const unsubs = sources.map((s) => s.subscribe(recompute));

  return {
    get() {
      return value;
    },

    subscribe(listener: Listener<T>): CleanupFn {
      listeners.add(listener);

      return () => {
        listeners.delete(listener);
      };
    },

    dispose() {
      for (const unsub of unsubs) {
        unsub();
      }

      unsubs.length = 0;
      listeners.clear();
    },
  };
}

export function effect<S extends Store<unknown>[]>(
  sources: [...S],
  fn: (
    ...values: {
      [K in keyof S]: S[K] extends Store<infer V> ? V : never;
    }
  ) => void | CleanupFn
): CleanupFn {
  let cleanup: CleanupFn | void;

  function run(): void {
    if (cleanup) cleanup();

    const values = sources.map((s) => s.get()) as {
      [K in keyof S]: S[K] extends Store<infer V> ? V : never;
    };

    cleanup = fn(...values);
  }

  run();

  const unsubs = sources.map((s) => s.subscribe(run));

  return () => {
    if (cleanup) cleanup();

    for (const unsub of unsubs) {
      unsub();
    }
  };
}
