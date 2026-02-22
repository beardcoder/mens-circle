/**
 * Umami Analytics Tracking Utility
 * Provides type-safe event tracking for Umami Analytics
 */

declare global {
  interface Window {
    umami?: {
      track: (eventName: string, eventData?: Record<string, unknown>) => void;
    };
  }
}

export interface UmamiEventData {
  [key: string]: string | number | boolean | undefined;
}

/**
 * Track a custom event in Umami Analytics
 */
export function trackEvent(
  eventName: string,
  eventData?: UmamiEventData
): void {
  if (typeof window.umami === 'undefined') {
    if (import.meta.env.DEV) {
      // eslint-disable-next-line no-console
      console.debug('[Umami] Event:', eventName, eventData);
    }

    return;
  }

  try {
    window.umami.track(eventName, eventData);
  } catch (error) {
    if (import.meta.env.DEV) {
      // eslint-disable-next-line no-console
      console.error('[Umami] Tracking error:', error);
    }
  }
}

/**
 * Predefined event names for consistent tracking
 */
export const TRACKING_EVENTS = {
  NEWSLETTER_SUBMIT: 'newsletter-submit',
  NEWSLETTER_SUCCESS: 'newsletter-success',
  NEWSLETTER_ERROR: 'newsletter-error',
  NEWSLETTER_ABANDON_FILLED: 'newsletter-abandon-filled',

  EVENT_REGISTRATION_SUBMIT: 'event-registration-submit',
  EVENT_REGISTRATION_SUCCESS: 'event-registration-success',
  EVENT_REGISTRATION_ERROR: 'event-registration-error',
  EVENT_REGISTRATION_ABANDON_FILLED: 'event-registration-abandon-filled',

  TESTIMONIAL_SUBMIT: 'testimonial-submit',
  TESTIMONIAL_SUCCESS: 'testimonial-success',
  TESTIMONIAL_ERROR: 'testimonial-error',
  TESTIMONIAL_ABANDON_FILLED: 'testimonial-abandon-filled',

  CALENDAR_OPEN: 'calendar-open',
  CALENDAR_DOWNLOAD_ICS: 'calendar-download-ics',
  CALENDAR_DOWNLOAD_GOOGLE: 'calendar-download-google',

  CTA_CLICK: 'cta-click',
  SOCIAL_CLICK: 'social-click',
  WHATSAPP_CLICK: 'whatsapp-click',
  EXTERNAL_LINK: 'external-link',

  FAQ_EXPAND: 'faq-expand',
  SCROLL_DEPTH: 'scroll-depth',

  NAV_CLICK: 'nav-click',
  FOOTER_LINK: 'footer-link',
  CONTACT_CLICK: 'contact-click',
} as const;

/**
 * Track scroll depth at 25%, 50%, 75%, 100% milestones
 */
export function useScrollDepthTracking(): void {
  const milestones = [25, 50, 75, 100];
  const reached = new Set<number>();

  const checkScrollDepth = (): void => {
    const scrollHeight =
      document.documentElement.scrollHeight - window.innerHeight;

    if (scrollHeight <= 0) return;

    const percent = Math.round((window.scrollY / scrollHeight) * 100);

    for (const milestone of milestones) {
      if (percent >= milestone && !reached.has(milestone)) {
        reached.add(milestone);
        trackEvent(TRACKING_EVENTS.SCROLL_DEPTH, {
          depth: milestone,
          page: window.location.pathname,
        });
      }
    }
  };

  window.addEventListener('scroll', checkScrollDepth, { passive: true });
}

/**
 * Track clicks on external links automatically
 */
export function useExternalLinkTracking(): void {
  document.addEventListener('click', (e) => {
    const link = (e.target as HTMLElement).closest(
      'a[href]'
    ) as HTMLAnchorElement | null;

    if (!link) return;

    const href = link.href;

    if (
      !href ||
      href.startsWith('javascript:') ||
      href.startsWith('#') ||
      link.hasAttribute('data-umami-event')
    ) {
      return;
    }

    try {
      const url = new URL(href);

      if (url.hostname !== window.location.hostname) {
        trackEvent(TRACKING_EVENTS.EXTERNAL_LINK, {
          url: href,
          text: (link.textContent ?? '').trim().slice(0, 50),
        });
      }
    } catch {
      // Invalid URL, ignore
    }
  });
}
