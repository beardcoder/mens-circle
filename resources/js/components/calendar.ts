/**
 * Calendar Integration Alpine Component
 */

import type { EventData } from '@/types';
import { TRACKING_EVENTS, trackEvent } from '@/utils/umami';

function formatICSDate(date: string, time: string): string {
  const d = new Date(`${date}T${time}:00`);

  return d
    .toISOString()
    .replace(/[-:]/g, '')
    .replace(/\.\d{3}/, '');
}

function generateICS(event: EventData): string {
  const start = formatICSDate(event.startDate, event.startTime);
  const end = formatICSDate(event.endDate, event.endTime);
  const now = formatICSDate(
    new Date().toISOString().slice(0, 10),
    new Date().toISOString().slice(11, 16)
  );

  return `BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Männerkreis Niederbayern/ Straubing//DE
CALSCALE:GREGORIAN
METHOD:PUBLISH
BEGIN:VEVENT
DTSTART:${start}
DTEND:${end}
DTSTAMP:${now}
UID:${Date.now()}@maennerkreis-straubing.de
SUMMARY:${event.title}
DESCRIPTION:${event.description.replace(/\n/g, '\\n')}
LOCATION:${event.location}
STATUS:CONFIRMED
END:VEVENT
END:VCALENDAR`;
}

function generateGoogleCalendarUrl(event: EventData): string {
  const formatDate = (date: string, time: string): string =>
    `${date.replace(/-/g, '')}T${time.replace(':', '')}00`;

  const params = new URLSearchParams({
    action: 'TEMPLATE',
    text: event.title,
    dates: `${formatDate(event.startDate, event.startTime)}/${formatDate(event.endDate, event.endTime)}`,
    details: event.description,
    location: event.location,
    ctz: 'Europe/Berlin',
  });

  return `https://calendar.google.com/calendar/render?${params.toString()}`;
}

const FALLBACK_EVENT: EventData = {
  title: 'Männerkreis Niederbayern/ Straubing',
  description:
    'Treffen des Männerkreis Niederbayern/ Straubing. Ein Raum für echte Begegnung unter Männern.',
  location: 'Straubing (genaue Adresse nach Anmeldung)',
  startDate: '2025-01-24',
  startTime: '19:00',
  endDate: '2025-01-24',
  endTime: '21:30',
};

export function calendarIntegration() {
  return {
    isOpen: false,
    icsUrl: '',
    googleUrl: '',
    eventTitle: '',
    _icsBlob: null as string | null,

    init() {
      const el = (this as unknown as { $el: HTMLElement }).$el;
      const ds = el.dataset;

      const eventData: EventData = ds.eventTitle
        ? {
            title: ds.eventTitle,
            description: ds.eventDescription ?? '',
            location: ds.eventLocation ?? '',
            startDate: ds.eventStartDate ?? '',
            startTime: ds.eventStartTime ?? '',
            endDate: ds.eventEndDate ?? '',
            endTime: ds.eventEndTime ?? '',
          }
        : (window.eventData ?? FALLBACK_EVENT);

      this.eventTitle = eventData.title;

      const icsContent = generateICS(eventData);
      const blob = new Blob([icsContent], {
        type: 'text/calendar;charset=utf-8',
      });

      this._icsBlob = URL.createObjectURL(blob);
      this.icsUrl = this._icsBlob;
      this.googleUrl = generateGoogleCalendarUrl(eventData);
    },

    openModal(): void {
      this.isOpen = true;
      trackEvent(TRACKING_EVENTS.CALENDAR_OPEN, { event: this.eventTitle });
    },

    closeModal(): void {
      this.isOpen = false;
    },

    trackICS(): void {
      trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_ICS, {
        event: this.eventTitle,
      });
    },

    trackGoogle(): void {
      trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_GOOGLE, {
        event: this.eventTitle,
      });
    },

    destroy(): void {
      if (this._icsBlob) {
        URL.revokeObjectURL(this._icsBlob);
        this._icsBlob = null;
      }
    },
  };
}
