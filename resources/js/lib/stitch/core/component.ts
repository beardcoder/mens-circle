import type {
  ComponentFactory,
  ComponentInstance,
  CleanupFn,
} from '../utils/types';
import type { Store, Listener } from './store';
import { queryAll, setAria, uid } from '../utils/dom';

/**
 * Scoped context passed to every `defineComponent` setup function.
 */
export interface ComponentContext<O> {
  readonly el: HTMLElement;
  readonly options: Readonly<O>;
  query<E extends HTMLElement = HTMLElement>(selector: string): E | null;
  queryAll(selector: string): HTMLElement[];
  attr(name: string, fallback?: string): string | undefined;
  attrJson<T = unknown>(name: string): T | undefined;
  on<K extends keyof HTMLElementEventMap>(
    event: K,
    handler: (e: HTMLElementEventMap[K], delegate: HTMLElement) => void
  ): void;
  on<K extends keyof HTMLElementEventMap>(
    event: K,
    selector: string,
    handler: (e: HTMLElementEventMap[K], delegate: HTMLElement) => void
  ): void;
  aria(el: Element, attrs: Record<string, string | boolean>): void;
  uid(prefix?: string): string;
  onDestroy(fn: CleanupFn): void;
  emit(name: string, detail?: unknown): void;
  sync<T>(store: Store<T>, listener: Listener<T>): void;
  data<T = Record<string, unknown>>(): T;
}

/** Setup function signature for `defineComponent`. */
export type SetupFn<O> = (ctx: ComponentContext<O>) => void | CleanupFn;

/**
 * Define a reusable, tree-shakeable component.
 */
export function defineComponent<O extends object = Record<string, never>>(
  defaults: O,
  setup: SetupFn<O>
): (overrides?: Partial<O>) => ComponentFactory<Partial<O>> {
  return (overrides?: Partial<O>): ComponentFactory<Partial<O>> => {
    return (el: HTMLElement): ComponentInstance => {
      const options = { ...defaults, ...overrides } as O;
      const cleanups: CleanupFn[] = [];

      const ctx: ComponentContext<O> = {
        el,
        options,

        query<E extends HTMLElement = HTMLElement>(selector: string): E | null {
          return el.querySelector<E>(selector);
        },

        queryAll(selector: string): HTMLElement[] {
          return queryAll(selector, el);
        },

        attr(name: string, fallback?: string): string | undefined {
          return el.getAttribute(`data-${name}`) ?? fallback;
        },

        attrJson<T = unknown>(name: string): T | undefined {
          const raw = el.getAttribute(`data-${name}`);

          if (raw == null) return undefined;

          try {
            return JSON.parse(raw) as T;
          } catch {
            return undefined;
          }
        },

        on<K extends keyof HTMLElementEventMap>(
          event: K,
          selectorOrHandler:
            | string
            | ((e: HTMLElementEventMap[K], delegate: HTMLElement) => void),
          maybeHandler?: (
            e: HTMLElementEventMap[K],
            delegate: HTMLElement
          ) => void
        ): void {
          let selector: string | null = null;
          let handler: (
            e: HTMLElementEventMap[K],
            delegate: HTMLElement
          ) => void;

          if (typeof selectorOrHandler === 'string') {
            selector = selectorOrHandler;
            handler = maybeHandler!;
          } else {
            handler = selectorOrHandler;
          }

          const listener = (e: HTMLElementEventMap[K]): void => {
            if (selector) {
              const delegate = (e.target as HTMLElement).closest?.<HTMLElement>(
                selector
              );

              if (delegate && el.contains(delegate)) {
                handler(e, delegate);
              }
            } else {
              handler(e, el);
            }
          };

          el.addEventListener(event, listener as EventListener);
          cleanups.push(() =>
            el.removeEventListener(event, listener as EventListener)
          );
        },

        aria: setAria,
        uid,

        onDestroy(fn: CleanupFn) {
          cleanups.push(fn);
        },

        emit(name: string, detail?: unknown) {
          el.dispatchEvent(new CustomEvent(name, { detail, bubbles: true }));
        },

        sync<T>(store: Store<T>, listener: Listener<T>) {
          listener(store.get(), store.get());
          const unsub = store.subscribe(listener);

          cleanups.push(unsub);
        },

        data<T = Record<string, unknown>>(): T {
          const script = el.querySelector<HTMLScriptElement>(
            'script[type="application/json"]'
          );

          if (script?.textContent) {
            try {
              return JSON.parse(script.textContent) as T;
            } catch {
              // fall through
            }
          }

          const props = el.getAttribute('data-props');

          if (props) {
            try {
              return JSON.parse(props) as T;
            } catch {
              // fall through
            }
          }

          const result: Record<string, string> = {};

          for (const attr of Array.from(el.attributes)) {
            if (attr.name.startsWith('data-') && attr.name !== 'data-props') {
              const key = attr.name
                .slice(5)
                .replace(/-([a-z])/g, (_, c: string) => c.toUpperCase());

              result[key] = attr.value;
            }
          }

          return result as T;
        },
      };

      const teardown = setup(ctx);

      if (teardown) cleanups.push(teardown);

      return {
        destroy() {
          for (let i = cleanups.length - 1; i >= 0; i--) {
            cleanups[i]();
          }

          cleanups.length = 0;
        },
      };
    };
  };
}
