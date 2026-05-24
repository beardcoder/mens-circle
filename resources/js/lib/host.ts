/**
 * Host — tiny factory for progressively enhanced components
 *
 * Components are written as factories rather than classes — each is a
 * pure function that closes over its DOM refs and state. The `host`
 * object passed by composition handles listener cleanup via a single
 * shared `AbortController`, so the public API of every component is
 * just `{ destroy(): void }`.
 *
 *   function createMyThing(root: HTMLElement): Component {
 *     const host = createHost(root);
 *     const button = host.query<HTMLButtonElement>('.do-it');
 *
 *     host.on(button, 'click', () => doStuff());
 *     host.onWindow('scroll', onScroll, { passive: true });
 *
 *     return { destroy: host.destroy };
 *   }
 *
 *   // In app.ts:
 *   mountAll('[data-component="my-thing"]', createMyThing);
 *
 * No `this`, no inheritance, no lifecycle methods — just closures plus
 * an explicit destroy hook.
 */

export interface Host {
  /** The root DOM element this component is attached to. */
  readonly root: HTMLElement;

  /** AbortSignal that fires when `destroy()` is called. */
  readonly signal: AbortSignal;

  /** Add a listener to `target` (or no-op if target is null/undefined). */
  on<K extends keyof HTMLElementEventMap>(
    target: EventTarget | null | undefined,
    type: K,
    handler: (event: HTMLElementEventMap[K]) => void,
    options?: AddEventListenerOptions
  ): void;

  /** Add a window-level listener. */
  onWindow<K extends keyof WindowEventMap>(
    type: K,
    handler: (event: WindowEventMap[K]) => void,
    options?: AddEventListenerOptions
  ): void;

  /** Add a document-level listener. */
  onDocument<K extends keyof DocumentEventMap>(
    type: K,
    handler: (event: DocumentEventMap[K]) => void,
    options?: AddEventListenerOptions
  ): void;

  /** Query a single element, scoped to the component root by default. */
  query<T extends HTMLElement = HTMLElement>(
    selector: string,
    scope?: ParentNode
  ): T | null;

  /** Query every matching element, scoped to the component root by default. */
  queryAll<T extends HTMLElement = HTMLElement>(
    selector: string,
    scope?: ParentNode
  ): T[];

  /** Abort every listener registered through this host. */
  destroy(): void;
}

/** The public surface every component factory returns. */
export interface Component {
  destroy(): void;
}

export function createHost(root: HTMLElement): Host {
  const controller = new AbortController();
  const signal = controller.signal;

  return {
    root,
    get signal() {
      return signal;
    },
    on(target, type, handler, options = {}) {
      target?.addEventListener(type, handler as EventListener, {
        ...options,
        signal,
      });
    },
    onWindow(type, handler, options = {}) {
      window.addEventListener(type, handler as EventListener, {
        ...options,
        signal,
      });
    },
    onDocument(type, handler, options = {}) {
      document.addEventListener(type, handler as EventListener, {
        ...options,
        signal,
      });
    },
    query<T extends HTMLElement = HTMLElement>(
      selector: string,
      scope: ParentNode = root
    ): T | null {
      return scope.querySelector<T>(selector);
    },
    queryAll<T extends HTMLElement = HTMLElement>(
      selector: string,
      scope: ParentNode = root
    ): T[] {
      return Array.from(scope.querySelectorAll<T>(selector));
    },
    destroy() {
      controller.abort();
    },
  };
}

/**
 * Find every element matching `selector`, run `factory` on each, and
 * return the live components. Factories that return `null` or `void`
 * (e.g. when the root doesn't match runtime expectations) are skipped.
 */
export function mountAll<T extends Component | null | void>(
  selector: string,
  factory: (root: HTMLElement) => T
): Exclude<T, null | void>[] {
  const components: Exclude<T, null | void>[] = [];

  for (const el of document.querySelectorAll<HTMLElement>(selector)) {
    const component = factory(el);

    if (component != null) {
      components.push(component as Exclude<T, null | void>);
    }
  }

  return components;
}
