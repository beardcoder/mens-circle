/**
 * Calendar Composables
 * Handles calendar integration for ICS and Google Calendar export
 */

import type { EventData } from '../types';
import { TRACKING_EVENTS, trackEvent } from '../utils/umami';

export function useCalendarIntegration(): void {
  const addToCalendarBtn = document.getElementById('addToCalendar');
  const calendarModal = document.getElementById('calendarModal');
  const calendarICS = document.getElementById(
    'calendarICS'
  ) as HTMLAnchorElement | null;
  const calendarGoogle = document.getElementById(
    'calendarGoogle'
  ) as HTMLAnchorElement | null;

  if (!addToCalendarBtn) return;

  const eventData: EventData = window.eventData ?? {
    title: 'Männerkreis Niederbayern/ Straubing',
    description:
      'Treffen des Männerkreis Niederbayern/ Straubing. Ein Raum für echte Begegnung unter Männern.',
    location: 'Straubing (genaue Adresse nach Anmeldung)',
    startDate: '2025-01-24',
    startTime: '19:00',
    endDate: '2025-01-24',
    endTime: '21:30',
  };

  // Pre-generate URLs once instead of on every click
  let icsBlobUrl: string | null = null;

  if (calendarICS) {
    const icsContent = generateICS(eventData);
    const blob = new Blob([icsContent], {
      type: 'text/calendar;charset=utf-8',
    });
    icsBlobUrl = URL.createObjectURL(blob);
    calendarICS.href = icsBlobUrl;
  }

  if (calendarGoogle) {
    calendarGoogle.href = generateGoogleCalendarUrl(eventData);
  }

  addToCalendarBtn.addEventListener('click', () => {
    if (!calendarModal) return;

    trackEvent(TRACKING_EVENTS.CALENDAR_OPEN, {
      event: eventData.title,
    });

    calendarModal.classList.add('open');
  });

  calendarICS?.addEventListener('click', () => {
    trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_ICS, {
      event: eventData.title,
    });
  });

  calendarGoogle?.addEventListener('click', () => {
    trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_GOOGLE, {
      event: eventData.title,
    });
  });

  calendarModal?.addEventListener('click', (e) => {
    if (e.target === calendarModal) {
      calendarModal.classList.remove('open');
    }
  });
}

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
