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
 *
 * @param eventName - Name of the event to track
 * @param eventData - Optional data associated with the event
 */
export function trackEvent(
  eventName: string,
  eventData?: UmamiEventData
): void {
  if (typeof window.umami === 'undefined') {
    // Umami is not loaded, skip tracking
    if (import.meta.env.DEV) {
      // eslint-disable-next-line no-console
      console.debug('[Umami] Event:', eventName, eventData);
    }

    return;
  }

  try {
    window.umami.track(eventName, eventData);
  } catch (error) {
    // Silently fail in production, log in development
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
  // Newsletter events
  NEWSLETTER_SUBMIT: 'newsletter-submit',
  NEWSLETTER_SUCCESS: 'newsletter-success',
  NEWSLETTER_ERROR: 'newsletter-error',

  // Event registration events
  EVENT_REGISTRATION_SUBMIT: 'event-registration-submit',
  EVENT_REGISTRATION_SUCCESS: 'event-registration-success',
  EVENT_REGISTRATION_ERROR: 'event-registration-error',

  // Testimonial events
  TESTIMONIAL_SUBMIT: 'testimonial-submit',
  TESTIMONIAL_SUCCESS: 'testimonial-success',
  TESTIMONIAL_ERROR: 'testimonial-error',

  // Calendar events
  CALENDAR_OPEN: 'calendar-open',
  CALENDAR_DOWNLOAD_ICS: 'calendar-download-ics',
  CALENDAR_DOWNLOAD_GOOGLE: 'calendar-download-google',

  // Navigation events
  CTA_CLICK: 'cta-click',
  SOCIAL_CLICK: 'social-click',
  WHATSAPP_CLICK: 'whatsapp-click',
  EXTERNAL_LINK: 'external-link',

  // Engagement events
  FAQ_EXPAND: 'faq-expand',
  SCROLL_DEPTH: 'scroll-depth',
} as const;

/**
 * Track form submission
 */
export function trackFormSubmit(formName: string): void {
  trackEvent(`${formName}-submit`);
}

/**
 * Track form success
 */
export function trackFormSuccess(
  formName: string,
  data?: UmamiEventData
): void {
  trackEvent(`${formName}-success`, data);
}

/**
 * Track form error
 */
export function trackFormError(formName: string, errorMessage?: string): void {
  trackEvent(`${formName}-error`, { error: errorMessage });
}

/**
 * Track link click
 */
export function trackLinkClick(linkType: string, destination?: string): void {
  trackEvent(`${linkType}-click`, { destination });
}

/**
 * Track calendar interaction
 */
export function trackCalendar(action: string, eventTitle?: string): void {
  trackEvent(`calendar-${action}`, { event: eventTitle });
}
