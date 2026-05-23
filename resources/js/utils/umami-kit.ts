/**
 * Umami Kit
 *
 * Page-level engagement tracking layered on top of the bare `umami.track`
 * call. Drives: scroll-depth checkpoints, time-on-page heartbeat, idle/
 * active transitions, external-link clicks, section visibility and page
 * exit. Every listener is registered with the same `AbortSignal` so
 * `destroy()` releases everything in a single `controller.abort()`.
 *
 * Based on https://github.com/rhelmer/umami-kit, adapted to preserve the
 * project's named event constants and to avoid duplicate tracking.
 */

import {
  TRACKING_EVENTS,
  trackEvent,
  type UmamiEventData,
} from '@/utils/umami';

export interface UmamiKitOptions {
  scrollDepthThresholds?: number[];
  scrollDebounceMs?: number;
  heartbeatIntervalMs?: number;
  idleTimeoutMs?: number;
  autoTrackClicks?: boolean;
  clickSelector?: string;
  visibilityThreshold?: number;
  visibilitySelector?: string;
  debug?: boolean;
}

type ResolvedOptions = Required<UmamiKitOptions>;

interface UmamiKitState {
  startTime: number;
  lastActivityAt: number;
  isIdle: boolean;
  trackedScrollDepths: Set<number>;
  visibleElementsCount: number;
}

const DEFAULTS: ResolvedOptions = {
  scrollDepthThresholds: [25, 50, 75, 90, 100],
  scrollDebounceMs: 120,
  heartbeatIntervalMs: 30_000,
  idleTimeoutMs: 60_000,
  autoTrackClicks: false,
  clickSelector: '[data-umami-track]',
  visibilityThreshold: 0.5,
  visibilitySelector: 'section[id]',
  debug: false,
};

const ACTIVITY_EVENTS = [
  'mousedown',
  'mousemove',
  'keydown',
  'scroll',
  'touchstart',
  'click',
] as const;

const MAX_UMAMI_WAIT_ATTEMPTS = 80;

export class UmamiKit {
  private readonly options: ResolvedOptions;
  private readonly state: UmamiKitState;
  private readonly controller = new AbortController();
  private scrollDebounceTimer: number | null = null;
  private heartbeatTimer: number | null = null;
  private idleTimer: number | null = null;
  private visibilityObserver: IntersectionObserver | null = null;
  private readonly seenVisibleElements = new WeakSet<Element>();
  private pageExitTracked = false;
  private initialized = false;

  public constructor(options: UmamiKitOptions = {}) {
    this.options = { ...DEFAULTS, ...options };
    this.state = {
      startTime: Date.now(),
      lastActivityAt: Date.now(),
      isIdle: false,
      trackedScrollDepths: new Set<number>(),
      visibleElementsCount: 0,
    };
  }

  public init(): void {
    if (this.initialized) return;

    this.initialized = true;

    this.waitForUmami(() => {
      this.setupScrollTracking();
      this.setupTimeTracking();
      this.setupIdleTracking();
      this.setupClickTracking();
      this.setupVisibilityTracking();
      this.setupPageExitTracking();
      this.log('Initialized');
    });
  }

  public destroy(): void {
    this.controller.abort();

    if (this.scrollDebounceTimer !== null) {
      window.clearTimeout(this.scrollDebounceTimer);
      this.scrollDebounceTimer = null;
    }

    if (this.heartbeatTimer !== null) {
      window.clearInterval(this.heartbeatTimer);
      this.heartbeatTimer = null;
    }

    if (this.idleTimer !== null) {
      window.clearInterval(this.idleTimer);
      this.idleTimer = null;
    }

    this.visibilityObserver?.disconnect();
    this.visibilityObserver = null;
  }

  public getStats(): {
    timeOnPageSeconds: number;
    maxScrollDepth: number;
    scrollDepthsReached: number[];
    isIdle: boolean;
    visibleElementsCount: number;
  } {
    const reached = Array.from(this.state.trackedScrollDepths);

    return {
      timeOnPageSeconds: this.getTimeOnPageSeconds(),
      maxScrollDepth: reached.length > 0 ? Math.max(...reached) : 0,
      scrollDepthsReached: reached.sort((a, b) => a - b),
      isIdle: this.state.isIdle,
      visibleElementsCount: this.state.visibleElementsCount,
    };
  }

  // ─── Wiring ────────────────────────────────────────────────────────────

  private get signal(): AbortSignal {
    return this.controller.signal;
  }

  private setupScrollTracking(): void {
    window.addEventListener(
      'scroll',
      () => {
        if (this.scrollDebounceTimer !== null) {
          window.clearTimeout(this.scrollDebounceTimer);
        }

        this.scrollDebounceTimer = window.setTimeout(
          () => this.checkScrollDepth(),
          this.options.scrollDebounceMs
        );
      },
      { passive: true, signal: this.signal }
    );

    this.checkScrollDepth();
  }

  private setupTimeTracking(): void {
    this.heartbeatTimer = window.setInterval(() => {
      if (this.state.isIdle) return;

      const seconds = this.getTimeOnPageSeconds();

      trackEvent(TRACKING_EVENTS.TIME_ON_PAGE, {
        seconds,
        minutes: Math.round(seconds / 60),
        page: window.location.pathname,
      });
    }, this.options.heartbeatIntervalMs);
  }

  private setupIdleTracking(): void {
    const onActivity = (): void => {
      const now = Date.now();

      if (this.state.isIdle) {
        const idleDurationSeconds = Math.max(
          1,
          Math.round((now - this.state.lastActivityAt) / 1000)
        );

        this.state.isIdle = false;

        trackEvent(TRACKING_EVENTS.USER_ACTIVE, {
          idle_duration_seconds: idleDurationSeconds,
          page: window.location.pathname,
        });
      }

      this.state.lastActivityAt = now;
    };

    for (const eventName of ACTIVITY_EVENTS) {
      document.addEventListener(eventName, onActivity, {
        passive: true,
        signal: this.signal,
      });
    }

    this.idleTimer = window.setInterval(() => {
      if (this.state.isIdle) return;

      const idleDuration = Date.now() - this.state.lastActivityAt;

      if (idleDuration < this.options.idleTimeoutMs) return;

      this.state.isIdle = true;

      trackEvent(TRACKING_EVENTS.USER_IDLE, {
        active_before_idle_seconds: Math.round(
          this.options.idleTimeoutMs / 1000
        ),
        page: window.location.pathname,
      });
    }, 30_000);
  }

  private setupClickTracking(): void {
    document.addEventListener(
      'click',
      (event: MouseEvent) => {
        if (!(event.target instanceof Element)) return;

        if (this.options.autoTrackClicks) {
          const trackedElement = event.target.closest<HTMLElement>(
            this.options.clickSelector
          );

          if (trackedElement) {
            const eventName =
              trackedElement.dataset.umamiTrack ?? TRACKING_EVENTS.CLICK;

            trackEvent(eventName, this.collectElementData(trackedElement));
          }
        }

        const link = event.target.closest<HTMLAnchorElement>('a[href]');

        if (
          !link ||
          !this.isExternalLink(link.href) ||
          link.hasAttribute('data-umami-event')
        ) {
          return;
        }

        trackEvent(TRACKING_EVENTS.EXTERNAL_LINK, {
          ...this.collectElementData(link),
          url: link.href,
          text: (link.textContent ?? '').trim().slice(0, 80),
          page: window.location.pathname,
        });
      },
      { passive: true, signal: this.signal }
    );
  }

  private setupVisibilityTracking(): void {
    if (!('IntersectionObserver' in window)) return;

    const elements = document.querySelectorAll<HTMLElement>(
      this.options.visibilitySelector
    );

    if (elements.length === 0) return;

    this.visibilityObserver = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (!entry.isIntersecting) continue;

          const element = entry.target as HTMLElement;

          if (this.seenVisibleElements.has(element)) continue;

          this.seenVisibleElements.add(element);
          this.state.visibleElementsCount += 1;

          const eventName =
            element.dataset.umamiVisible ?? TRACKING_EVENTS.SECTION_VISIBLE;

          trackEvent(eventName, {
            ...this.collectElementData(element),
            element_id: element.id || 'unknown',
            visibility_ratio: Math.round(entry.intersectionRatio * 100),
            page: window.location.pathname,
          });
        }
      },
      { threshold: this.options.visibilityThreshold }
    );

    for (const element of elements) {
      this.visibilityObserver.observe(element);
    }
  }

  private setupPageExitTracking(): void {
    const onExit = (): void => this.trackPageExit();

    window.addEventListener('pagehide', onExit, {
      capture: true,
      signal: this.signal,
    });
    window.addEventListener('beforeunload', onExit, {
      capture: true,
      signal: this.signal,
    });
  }

  // ─── Internals ─────────────────────────────────────────────────────────

  private checkScrollDepth(): void {
    const scrollRange =
      document.documentElement.scrollHeight - window.innerHeight;

    if (scrollRange <= 0) return;

    const scrollPercent = Math.min(
      100,
      Math.round((window.scrollY / scrollRange) * 100)
    );

    for (const threshold of this.options.scrollDepthThresholds) {
      if (
        scrollPercent >= threshold &&
        !this.state.trackedScrollDepths.has(threshold)
      ) {
        this.state.trackedScrollDepths.add(threshold);

        trackEvent(TRACKING_EVENTS.SCROLL_DEPTH, {
          depth: threshold,
          percentage: `${threshold}%`,
          pixels: Math.round(window.scrollY),
          page: window.location.pathname,
        });
      }
    }
  }

  private trackPageExit(): void {
    if (this.pageExitTracked) return;

    this.pageExitTracked = true;

    const reachedDepths = Array.from(this.state.trackedScrollDepths);

    trackEvent(TRACKING_EVENTS.PAGE_EXIT, {
      total_time_seconds: this.getTimeOnPageSeconds(),
      max_scroll_depth:
        reachedDepths.length > 0 ? Math.max(...reachedDepths) : 0,
      scroll_depth_count: reachedDepths.length,
      page: window.location.pathname,
    });
  }

  private collectElementData(element: HTMLElement): UmamiEventData {
    const eventData: UmamiEventData = {};

    for (const [key, value] of Object.entries(element.dataset)) {
      if (!key.startsWith('umamiData') || !value) continue;

      const rawKey = key.slice('umamiData'.length);

      if (!rawKey) continue;

      const normalizedKey = rawKey.charAt(0).toLowerCase() + rawKey.slice(1);

      eventData[normalizedKey] = value;
    }

    eventData.element = element.tagName.toLowerCase();

    if (element.id) eventData.element_id = element.id;

    if (element.className) {
      eventData.element_classes = String(element.className).slice(0, 120);
    }

    const text = (element.textContent ?? '').trim();

    if (text) eventData.text = text.slice(0, 80);

    if (element instanceof HTMLAnchorElement && element.href) {
      eventData.href = element.href;
    }

    return eventData;
  }

  private isExternalLink(url: string): boolean {
    try {
      const parsedUrl = new URL(url, window.location.origin);

      return parsedUrl.hostname !== window.location.hostname;
    } catch {
      return false;
    }
  }

  private getTimeOnPageSeconds(): number {
    return Math.max(1, Math.round((Date.now() - this.state.startTime) / 1000));
  }

  private waitForUmami(callback: () => void, attempts = 0): void {
    if (typeof window.umami?.track === 'function') {
      callback();

      return;
    }

    if (attempts >= MAX_UMAMI_WAIT_ATTEMPTS) {
      this.log(
        'Umami script not detected in time, continuing and tracking when available'
      );
      callback();

      return;
    }

    window.setTimeout(() => this.waitForUmami(callback, attempts + 1), 100);
  }

  private log(...args: unknown[]): void {
    if (!this.options.debug || !import.meta.env.DEV) return;

    // eslint-disable-next-line no-console
    console.debug('[UmamiKit]', ...args);
  }
}

declare global {
  interface Window {
    umamiTracker?: UmamiKit;
  }
}

export function initUmamiKit(options: UmamiKitOptions = {}): UmamiKit {
  const tracker = new UmamiKit(options);

  tracker.init();
  window.umamiTracker = tracker;

  return tracker;
}
