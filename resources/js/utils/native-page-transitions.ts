/**
 * Native cross-document page transitions powered by the View Transitions API.
 *
 * The CSS layer already enables `navigation: auto`; this file only annotates
 * supported navigations with semantic transition types so route-specific motion
 * variants can opt in without a JavaScript router.
 */

type NativePageTransitionType = 'to-event' | 'from-event';

interface NativeNavigationEntry {
  url?: string;
}

interface NativeNavigationActivation {
  entry?: NativeNavigationEntry | null;
  from?: NativeNavigationEntry | null;
}

interface NativeViewTransition {
  types?: Set<string>;
}

interface NativePageTransitionEvent extends Event {
  activation?: NativeNavigationActivation | null;
  viewTransition?: NativeViewTransition | null;
}

function isEventPage(url: string | undefined): boolean {
  if (!url) {
    return false;
  }

  const pathname =
    new URL(url, globalThis.location.origin).pathname.replace(/\/+$/, '') ||
    '/';

  return pathname === '/event' || pathname.startsWith('/event/');
}

function resolveTransitionType(
  fromUrl: string | undefined,
  toUrl: string | undefined
): NativePageTransitionType | null {
  const isFromEventPage = isEventPage(fromUrl);
  const isToEventPage = isEventPage(toUrl);

  if (!isFromEventPage && isToEventPage) {
    return 'to-event';
  }

  if (isFromEventPage && !isToEventPage) {
    return 'from-event';
  }

  return null;
}

function applyTransitionType(
  event: NativePageTransitionEvent,
  fromUrl: string | undefined,
  toUrl: string | undefined
): void {
  const transitionType = resolveTransitionType(fromUrl, toUrl);

  if (!transitionType) {
    return;
  }

  event.viewTransition?.types?.add(transitionType);
}

export function initNativePageTransitions(): void {
  if (!('onpageswap' in window) || !('onpagereveal' in window)) {
    return;
  }

  window.addEventListener('pageswap', (event) => {
    const nativeEvent = event as NativePageTransitionEvent;

    applyTransitionType(
      nativeEvent,
      globalThis.location.href,
      nativeEvent.activation?.entry?.url
    );
  });

  window.addEventListener('pagereveal', (event) => {
    const nativeEvent = event as NativePageTransitionEvent;

    applyTransitionType(
      nativeEvent,
      nativeEvent.activation?.from?.url,
      globalThis.location.href
    );
  });
}
