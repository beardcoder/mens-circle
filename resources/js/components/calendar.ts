/**
 * Calendar Integration
 *
 * Generates a local ICS blob URL plus a Google Calendar deep link so the
 * "Add to calendar" modal can offer both options without a network round
 * trip. Tracks user choice via Umami. Vanilla TS, no framework.
 */

import { mountAll, ReactiveHost } from '@/lib/reactive-host';
import { TRACKING_EVENTS, trackEvent } from '@/utils/umami';
import type { EventData } from '@/types';

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

class Calendar extends ReactiveHost {
  private isOpen = false;
  private eventTitle = '';
  private modal: HTMLElement | null = null;
  private openButton: HTMLButtonElement | null = null;
  private icsBlobUrl: string | null = null;

  protected setup(): void {
    const event = readEventFromDataset(this.root);

    this.eventTitle = event.title;

    const blob = new Blob([generateICS(event)], {
      type: 'text/calendar;charset=utf-8',
    });

    this.icsBlobUrl = URL.createObjectURL(blob);

    const googleUrl = generateGoogleCalendarUrl(event);
    const googleLink = this.query<HTMLAnchorElement>('[data-ref="google-url"]');
    const icsLink = this.query<HTMLAnchorElement>('[data-ref="ics-url"]');

    if (googleLink) googleLink.href = googleUrl;
    if (icsLink) icsLink.href = this.icsBlobUrl;

    this.modal = this.query('[data-ref="modal"]');
    this.openButton = this.query<HTMLButtonElement>('[data-action="open"]');

    this.on(this.openButton, 'click', () => {
      this.isOpen = true;
      trackEvent(TRACKING_EVENTS.CALENDAR_OPEN, { event: this.eventTitle });
      this.render();
    });

    this.on(this.modal, 'click', (event) => {
      if (event.target === this.modal) this.close();
    });

    this.onWindow('keydown', (event) => {
      if (event.key === 'Escape' && this.isOpen) this.close();
    });

    this.on(googleLink, 'click', () =>
      trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_GOOGLE, {
        event: this.eventTitle,
      })
    );

    this.on(icsLink, 'click', () =>
      trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_ICS, {
        event: this.eventTitle,
      })
    );
  }

  protected render(): void {
    if (!this.modal) return;

    this.modal.classList.toggle('open', this.isOpen);
    this.modal.style.display = this.isOpen ? 'flex' : 'none';
  }

  protected teardown(): void {
    if (this.icsBlobUrl) {
      URL.revokeObjectURL(this.icsBlobUrl);
      this.icsBlobUrl = null;
    }
  }

  private close(): void {
    this.isOpen = false;
    this.render();
  }
}

export function setupCalendar(): void {
  mountAll('[data-component="calendar"]', (el) => new Calendar(el));
}
