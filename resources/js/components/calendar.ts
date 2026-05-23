/**
 * Calendar Integration Alpine Component
 *
 * Generates a local ICS blob URL plus a Google Calendar deep link so the
 * "Add to calendar" modal can offer both options without a network round
 * trip. Tracks user choice via Umami.
 */

import type { EventData } from '@/types';
import type { AlpineMagics } from '@/types/alpine';
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
  const stamp = formatICSDate(
    new Date().toISOString().slice(0, 10),
    new Date().toISOString().slice(11, 16)
  );

  return [
    'BEGIN:VCALENDAR',
    'VERSION:2.0',
    'PRODID:-//Männerkreis Niederbayern/ Straubing//DE',
    'CALSCALE:GREGORIAN',
    'METHOD:PUBLISH',
    'BEGIN:VEVENT',
    `DTSTART:${start}`,
    `DTEND:${end}`,
    `DTSTAMP:${stamp}`,
    `UID:${Date.now()}@maennerkreis-straubing.de`,
    `SUMMARY:${event.title}`,
    `DESCRIPTION:${event.description.replace(/\n/g, '\\n')}`,
    `LOCATION:${event.location}`,
    'STATUS:CONFIRMED',
    'END:VEVENT',
    'END:VCALENDAR',
  ].join('\n');
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

function readEventFromDataset(el: HTMLElement): EventData {
  const ds = el.dataset;

  if (ds.eventTitle) {
    return {
      title: ds.eventTitle,
      description: ds.eventDescription ?? '',
      location: ds.eventLocation ?? '',
      startDate: ds.eventStartDate ?? '',
      startTime: ds.eventStartTime ?? '',
      endDate: ds.eventEndDate ?? '',
      endTime: ds.eventEndTime ?? '',
    };
  }

  return window.eventData ?? FALLBACK_EVENT;
}

export function calendarIntegration() {
  return {
    isOpen: false,
    icsUrl: '',
    googleUrl: '',
    eventTitle: '',
    _icsBlobUrl: null as string | null,

    init(this: AlpineMagics & { [k: string]: unknown }) {
      const event = readEventFromDataset(this.$el);

      this.eventTitle = event.title;

      const blob = new Blob([generateICS(event)], {
        type: 'text/calendar;charset=utf-8',
      });

      this._icsBlobUrl = URL.createObjectURL(blob);
      this.icsUrl = this._icsBlobUrl;
      this.googleUrl = generateGoogleCalendarUrl(event);
    },

    openModal(this: { isOpen: boolean; eventTitle: string }) {
      this.isOpen = true;
      trackEvent(TRACKING_EVENTS.CALENDAR_OPEN, { event: this.eventTitle });
    },

    closeModal(this: { isOpen: boolean }) {
      this.isOpen = false;
    },

    trackICS(this: { eventTitle: string }) {
      trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_ICS, {
        event: this.eventTitle,
      });
    },

    trackGoogle(this: { eventTitle: string }) {
      trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_GOOGLE, {
        event: this.eventTitle,
      });
    },

    destroy(this: { _icsBlobUrl: string | null }) {
      if (this._icsBlobUrl) {
        URL.revokeObjectURL(this._icsBlobUrl);
        this._icsBlobUrl = null;
      }
    },
  };
}
