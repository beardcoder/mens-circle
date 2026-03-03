import type {
  ComponentFactory,
  ComponentInstance,
  EnhanceOptions,
} from '../utils/types';
import { queryAll } from '../utils/dom';

/** Marker attribute so we never double-enhance the same element+factory pair. */
const ENHANCED = 'data-stitch-enhanced';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
type AnyFactory = ComponentFactory<any>;

/** WeakMap tracking active instances per element. */
const instances = new WeakMap<
  HTMLElement,
  Map<AnyFactory, ComponentInstance>
>();

/**
 * Enhance all elements matching `selector` with the given component `factory`.
 * Idempotent: calling twice on the same element is a no-op.
 */
export function enhance<O>(
  selector: string,
  factory: ComponentFactory<O>,
  opts?: EnhanceOptions<O>
): ComponentInstance[] {
  const root = opts?.root ?? document;
  const elements = queryAll(selector, root);
  const results: ComponentInstance[] = [];

  for (const el of elements) {
    let map = instances.get(el);

    if (map?.has(factory)) continue;

    const instance = factory(el, opts?.options);

    if (!map) {
      map = new Map();
      instances.set(el, map);
    }

    map.set(factory, instance);
    el.setAttribute(ENHANCED, '');

    const originalDestroy = instance.destroy;

    instance.destroy = () => {
      originalDestroy();
      map!.delete(factory);

      if (map!.size === 0) {
        instances.delete(el);
        el.removeAttribute(ENHANCED);
      }
    };

    results.push(instance);
  }

  return results;
}

/**
 * Destroy all stitch instances on elements matching `selector`.
 */
export function destroyAll(
  selector: string,
  factory?: ComponentFactory,
  root: ParentNode = document
): void {
  const elements = queryAll(selector, root);

  for (const el of elements) {
    const map = instances.get(el);

    if (!map) continue;

    if (factory) {
      map.get(factory)?.destroy();
    } else {
      for (const instance of map.values()) {
        instance.destroy();
      }
    }
  }
}
