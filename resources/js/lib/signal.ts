/**
 * Signals — minimal reactive primitive with auto-tracking effects
 *
 * Same shape as Svelte 5 runes / Preact signals: a signal is a callable
 * that returns its current value (`count()`) and exposes setters. Reads
 * inside an `effect(...)` register the effect as a subscriber; whenever
 * the signal's value changes, every effect that depends on it re-runs.
 *
 *   const count = signal(0);
 *   const double = derived(() => count() * 2);
 *
 *   effect(() => {
 *     el.textContent = `${count()} → ${double()}`;
 *   }, host.signal);  // cleanup tied to the host's AbortSignal
 *
 *   count.set(1);     // effect re-runs automatically
 *   count.update((n) => n + 1);
 *
 * Designed to be tree-shakeable: the auto-tracking machinery lives at
 * module scope but is dead-code-eliminable if a bundle never imports
 * `effect`/`derived`.
 */

interface Effect {
  run(): void;
  dispose(): void;
}

let currentEffect: Effect | null = null;

export interface Signal<T> {
  (): T;
  /** Replace the current value. No-op if the new value is identical. */
  set(value: T): void;
  /** Apply a mutator to the current value and broadcast the result. */
  update(fn: (value: T) => T): void;
  /** Subscribe imperatively. Returns an unsubscribe handle. */
  subscribe(fn: (value: T) => void): () => void;
}

export interface ReadonlySignal<T> {
  (): T;
  subscribe(fn: (value: T) => void): () => void;
}

interface Subscribable {
  _track(effect: Effect): void;
}

/** Create a writable signal. */
export function signal<T>(initial: T): Signal<T> {
  let value = initial;
  const effects = new Set<Effect>();
  const callbacks = new Set<(value: T) => void>();

  const node = (() => {
    if (currentEffect !== null) effects.add(currentEffect);

    return value;
  }) as Signal<T> & Subscribable;

  node.set = (next: T): void => {
    if (Object.is(next, value)) return;

    value = next;

    // Snapshot subscribers so that effects mutating the set during run
    // (e.g. self-disposing effects) don't break the iteration.
    for (const effect of [...effects]) effect.run();
    for (const cb of [...callbacks]) cb(value);
  };

  node.update = (fn: (value: T) => T): void => node.set(fn(value));

  node.subscribe = (fn: (value: T) => void): (() => void) => {
    callbacks.add(fn);
    fn(value);

    return () => {
      callbacks.delete(fn);
    };
  };

  node._track = (effect: Effect): void => {
    effects.add(effect);
  };

  return node;
}

/**
 * Derived signal — read-only, recomputes via auto-tracking whenever any
 * upstream signal it reads from changes.
 */
export function derived<T>(compute: () => T): ReadonlySignal<T> {
  const inner = signal<T>(compute());

  effect(() => {
    inner.set(compute());
  });

  const node = (() => inner()) as ReadonlySignal<T>;

  node.subscribe = inner.subscribe;

  return node;
}

/**
 * Run `fn` immediately and again whenever any signal it reads from
 * changes. Pass an `AbortSignal` (typically `host.signal`) to dispose
 * the effect when the owning component tears down.
 */
export function effect(fn: () => void, signal?: AbortSignal): () => void {
  const subscriptions = new Set<Subscribable>();
  let disposed = false;

  const e: Effect = {
    run(): void {
      if (disposed) return;

      const prev = currentEffect;

      currentEffect = e;
      try {
        fn();
      } finally {
        currentEffect = prev;
      }
    },
    dispose(): void {
      disposed = true;
      subscriptions.clear();
    },
  };

  // Wrap `run` so that during the first execution we capture every
  // signal that was read, then on subsequent runs the same logic
  // re-collects them. Each `signal._track` adds the effect to the
  // signal's internal set, so the relationship is implicit.
  e.run();

  if (signal) {
    signal.addEventListener('abort', () => e.dispose(), { once: true });
  }

  return () => e.dispose();
}
