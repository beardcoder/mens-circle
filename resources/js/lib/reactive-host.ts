/**
 * Reactive Host — tiny base class for progressively enhanced components
 *
 * Each component picks a root `HTMLElement` and inherits:
 *   - a single `AbortController` for all listener cleanup
 *   - a typed `on()` helper that automatically attaches `signal`
 *   - `query()` / `queryAll()` scoped to the component root
 *   - lifecycle: `setup()` runs once on mount, `render()` is called manually
 *     after any state change to update the DOM
 *
 * Components are wired up imperatively in `app.ts` via factory functions that
 * find their root element and call `new MyComponent(root).init()`. No global
 * registry, no framework, no Alpine.
 */

export abstract class ReactiveHost {
  protected readonly root: HTMLElement;
  protected readonly controller = new AbortController();

  public constructor(root: HTMLElement) {
    this.root = root;
  }

  public init(): void {
    this.setup();
    this.render();
  }

  public destroy(): void {
    this.controller.abort();
    this.teardown();
  }

  protected get signal(): AbortSignal {
    return this.controller.signal;
  }

  protected on<K extends keyof HTMLElementEventMap>(
    target: EventTarget | null,
    type: K,
    handler: (event: HTMLElementEventMap[K]) => void,
    options: AddEventListenerOptions = {}
  ): void {
    target?.addEventListener(type, handler as EventListener, {
      ...options,
      signal: this.signal,
    });
  }

  protected onWindow<K extends keyof WindowEventMap>(
    type: K,
    handler: (event: WindowEventMap[K]) => void,
    options: AddEventListenerOptions = {}
  ): void {
    window.addEventListener(type, handler as EventListener, {
      ...options,
      signal: this.signal,
    });
  }

  protected onDocument<K extends keyof DocumentEventMap>(
    type: K,
    handler: (event: DocumentEventMap[K]) => void,
    options: AddEventListenerOptions = {}
  ): void {
    document.addEventListener(type, handler as EventListener, {
      ...options,
      signal: this.signal,
    });
  }

  protected query<T extends HTMLElement = HTMLElement>(
    selector: string,
    scope: ParentNode = this.root
  ): T | null {
    return scope.querySelector<T>(selector);
  }

  protected queryAll<T extends HTMLElement = HTMLElement>(
    selector: string,
    scope: ParentNode = this.root
  ): T[] {
    return Array.from(scope.querySelectorAll<T>(selector));
  }

  /** Override to wire up listeners and capture refs. Called once on init. */
  protected abstract setup(): void;

  /** Override to update DOM from current state. Called after every state mutation. */
  protected render(): void {}

  /** Override for any cleanup that's not listener-related (e.g. revoke blob URLs). */
  protected teardown(): void {}
}

/**
 * Find every element matching `selector`, construct a host for it, and
 * return the live instances. No-op if none are found.
 */
export function mountAll<T extends ReactiveHost>(
  selector: string,
  factory: (el: HTMLElement) => T
): T[] {
  return Array.from(document.querySelectorAll<HTMLElement>(selector)).map(
    (el) => {
      const host = factory(el);

      host.init();

      return host;
    }
  );
}
