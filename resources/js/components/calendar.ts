/**
 * Calendar Integration
 *
 * Generates an ICS blob URL + Google Calendar deep link from event data
 * attributes on the root element, then wires the "Add to calendar"
 * button + modal to expose both options. Factory style.
 */

import { TRACKING_EVENTS, trackEvent } from '@/utils/umami';
import { defineComponent } from '@beardcoder/lume';
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

export default defineComponent(
  ({ root, on, part, signal, effect, cleanup }) => {
    const event = readEventFromDataset(root);
    const blob = new Blob([generateICS(event)], {
      type: 'text/calendar;charset=utf-8',
    });
    const icsBlobUrl = URL.createObjectURL(blob);
    const googleUrl = generateGoogleCalendarUrl(event);

    const modal = part('modal');
    const openBtn = root.querySelector<HTMLButtonElement>(
      '[data-action="open"]'
    );
    const googleLink = part('google-url') as HTMLAnchorElement;
    const icsLink = part('ics-url') as HTMLAnchorElement;

    googleLink.href = googleUrl;
    icsLink.href = icsBlobUrl;

    const isOpen = signal(false);

    effect(() => {
      modal.classList.toggle('open', isOpen());
      modal.style.display = isOpen() ? 'flex' : 'none';
    });

    const close = (): void => {
      isOpen.set(false);
    };

    if (openBtn) {
      on(openBtn, 'click', () => {
        isOpen.set(true);
        trackEvent(TRACKING_EVENTS.CALENDAR_OPEN, { event: event.title });
      });
    }

    on(modal, 'click', (e) => {
      if (e.target === modal) close();
    });

    on(window, 'keydown', (e) => {
      if ((e as KeyboardEvent).key === 'Escape' && isOpen()) close();
    });

    on(googleLink, 'click', () =>
      trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_GOOGLE, {
        event: event.title,
      })
    );

    on(icsLink, 'click', () =>
      trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_ICS, { event: event.title })
    );

    cleanup(() => URL.revokeObjectURL(icsBlobUrl));

    return {};
  }
);
