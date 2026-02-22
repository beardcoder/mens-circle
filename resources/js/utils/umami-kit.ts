/**
 * Umami Kit
 * Based on: https://github.com/rhelmer/umami-kit
 * Adapted for this project to avoid duplicate tracking and preserve event names.
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

interface RequiredUmamiKitOptions {
  scrollDepthThresholds: number[];
  scrollDebounceMs: number;
  heartbeatIntervalMs: number;
  idleTimeoutMs: number;
  autoTrackClicks: boolean;
  clickSelector: string;
  visibilityThreshold: number;
  visibilitySelector: string;
  debug: boolean;
}

interface UmamiKitState {
  startTime: number;
  lastActivityAt: number;
  isIdle: boolean;
  trackedScrollDepths: Set<number>;
  visibleElementsCount: number;
}

export class UmamiKit {
  private readonly options: RequiredUmamiKitOptions;
  private readonly state: UmamiKitState;
  private scrollDebounceTimer: number | null = null;
  private heartbeatTimer: number | null = null;
  private idleTimer: number | null = null;
  private visibilityObserver: IntersectionObserver | null = null;
  private readonly seenVisibleElements = new WeakSet<Element>();
  private pageExitTracked = false;
  private initialized = false;
  private readonly maxUmamiWaitAttempts = 80;

  public constructor(options: UmamiKitOptions = {}) {
    this.options = {
      scrollDepthThresholds: [25, 50, 75, 90, 100],
      scrollDebounceMs: 120,
      heartbeatIntervalMs: 30_000,
      idleTimeoutMs: 60_000,
      autoTrackClicks: false,
      clickSelector: '[data-umami-track]',
      visibilityThreshold: 0.5,
      visibilitySelector: 'section[id]',
      debug: false,
      ...options,
    };

    this.state = {
      startTime: Date.now(),
      lastActivityAt: Date.now(),
      isIdle: false,
      trackedScrollDepths: new Set<number>(),
      visibleElementsCount: 0,
    };
  }

  public init(): void {
    if (this.initialized) {
      return;
    }

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

    if (this.visibilityObserver) {
      this.visibilityObserver.disconnect();
      this.visibilityObserver = null;
    }

    window.removeEventListener('scroll', this.handleScroll, { capture: false });
    document.removeEventListener('click', this.handleClick, { capture: false });
    window.removeEventListener('pagehide', this.handlePageHide, {
      capture: true,
    });
    window.removeEventListener('beforeunload', this.handleBeforeUnload, {
      capture: true,
    });

    [
      'mousedown',
      'mousemove',
      'keydown',
      'scroll',
      'touchstart',
      'click',
    ].forEach((eventName) => {
      document.removeEventListener(eventName, this.handleActivity, {
        capture: false,
      });
    });
  }

  public getStats(): {
    timeOnPageSeconds: number;
    maxScrollDepth: number;
    scrollDepthsReached: number[];
    isIdle: boolean;
    visibleElementsCount: number;
  } {
    return {
      timeOnPageSeconds: this.getTimeOnPageSeconds(),
      maxScrollDepth: Math.max(
        0,
        ...Array.from(this.state.trackedScrollDepths)
      ),
      scrollDepthsReached: Array.from(this.state.trackedScrollDepths).sort(
        (a, b) => a - b
      ),
      isIdle: this.state.isIdle,
      visibleElementsCount: this.state.visibleElementsCount,
    };
  }

  private waitForUmami(callback: () => void, attempts = 0): void {
    if (typeof window.umami?.track === 'function') {
      callback();

      return;
    }

    if (attempts >= this.maxUmamiWaitAttempts) {
      this.log(
        'Umami script not detected in time, continuing and tracking when available'
      );
      callback();

      return;
    }

    window.setTimeout(() => this.waitForUmami(callback, attempts + 1), 100);
  }

  private log(...args: unknown[]): void {
    if (!this.options.debug || !import.meta.env.DEV) {
      return;
    }

    // eslint-disable-next-line no-console
    console.debug('[UmamiKit]', ...args);
  }

  private readonly handleScroll = (): void => {
    if (this.scrollDebounceTimer !== null) {
      window.clearTimeout(this.scrollDebounceTimer);
    }

    this.scrollDebounceTimer = window.setTimeout(() => {
      this.checkScrollDepth();
    }, this.options.scrollDebounceMs);
  };

  private setupScrollTracking(): void {
    window.addEventListener('scroll', this.handleScroll, { passive: true });
    this.checkScrollDepth();
  }

  private checkScrollDepth(): void {
    const scrollRange =
      document.documentElement.scrollHeight - window.innerHeight;

    if (scrollRange <= 0) {
      return;
    }

    const scrollPercent = Math.min(
      100,
      Math.round((window.scrollY / scrollRange) * 100)
    );

    this.options.scrollDepthThresholds.forEach((threshold) => {
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
    });
  }

  private setupTimeTracking(): void {
    this.heartbeatTimer = window.setInterval(() => {
      if (this.state.isIdle) {
        return;
      }

      const timeOnPageSeconds = this.getTimeOnPageSeconds();

      trackEvent(TRACKING_EVENTS.TIME_ON_PAGE, {
        seconds: timeOnPageSeconds,
        minutes: Math.round(timeOnPageSeconds / 60),
        page: window.location.pathname,
      });
    }, this.options.heartbeatIntervalMs);
  }

  private readonly handleActivity = (): void => {
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

  private setupIdleTracking(): void {
    [
      'mousedown',
      'mousemove',
      'keydown',
      'scroll',
      'touchstart',
      'click',
    ].forEach((eventName) => {
      document.addEventListener(eventName, this.handleActivity, {
        passive: true,
      });
    });

    this.idleTimer = window.setInterval(() => {
      if (this.state.isIdle) {
        return;
      }

      const idleDuration = Date.now() - this.state.lastActivityAt;

      if (idleDuration < this.options.idleTimeoutMs) {
        return;
      }

      this.state.isIdle = true;

      trackEvent(TRACKING_EVENTS.USER_IDLE, {
        active_before_idle_seconds: Math.round(
          this.options.idleTimeoutMs / 1000
        ),
        page: window.location.pathname,
      });
    }, 30_000);
  }

  private readonly handleClick = (event: MouseEvent): void => {
    if (!(event.target instanceof Element)) {
      return;
    }

    if (this.options.autoTrackClicks) {
      const trackedElement = event.target.closest(
        this.options.clickSelector
      ) as HTMLElement | null;

      if (trackedElement) {
        const eventName =
          trackedElement.dataset.umamiTrack ?? TRACKING_EVENTS.CLICK;

        trackEvent(eventName, this.collectElementData(trackedElement));
      }
    }

    const link = event.target.closest('a[href]') as HTMLAnchorElement | null;

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
  };

  private setupClickTracking(): void {
    document.addEventListener('click', this.handleClick, {
      passive: true,
    });
  }

  private setupVisibilityTracking(): void {
    if (!('IntersectionObserver' in window)) {
      return;
    }

    const elements = document.querySelectorAll<HTMLElement>(
      this.options.visibilitySelector
    );

    if (elements.length === 0) {
      return;
    }

    this.visibilityObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) {
            return;
          }

          const element = entry.target as HTMLElement;

          if (this.seenVisibleElements.has(element)) {
            return;
          }

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
        });
      },
      {
        threshold: this.options.visibilityThreshold,
      }
    );

    elements.forEach((element) => {
      this.visibilityObserver?.observe(element);
    });
  }

  private setupPageExitTracking(): void {
    window.addEventListener('pagehide', this.handlePageHide, {
      capture: true,
    });
    window.addEventListener('beforeunload', this.handleBeforeUnload, {
      capture: true,
    });
  }

  private readonly handlePageHide = (): void => {
    this.trackPageExit();
  };

  private readonly handleBeforeUnload = (): void => {
    this.trackPageExit();
  };

  private trackPageExit(): void {
    if (this.pageExitTracked) {
      return;
    }

    this.pageExitTracked = true;

    const reachedDepths = Array.from(this.state.trackedScrollDepths);

    trackEvent(TRACKING_EVENTS.PAGE_EXIT, {
      total_time_seconds: this.getTimeOnPageSeconds(),
      max_scroll_depth: Math.max(0, ...reachedDepths),
      scroll_depth_count: reachedDepths.length,
      page: window.location.pathname,
    });
  }

  private collectElementData(element: HTMLElement): UmamiEventData {
    const eventData: UmamiEventData = {};

    Object.entries(element.dataset).forEach(([key, value]) => {
      if (!key.startsWith('umamiData') || !value) {
        return;
      }

      const rawKey = key.slice('umamiData'.length);

      if (!rawKey) {
        return;
      }

      const normalizedKey = rawKey.charAt(0).toLowerCase() + rawKey.slice(1);

      eventData[normalizedKey] = value;
    });

    eventData.element = element.tagName.toLowerCase();

    if (element.id) {
      eventData.element_id = element.id;
    }

    if (element.className) {
      eventData.element_classes = String(element.className).slice(0, 120);
    }

    const text = (element.textContent ?? '').trim();

    if (text) {
      eventData.text = text.slice(0, 80);
    }

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
