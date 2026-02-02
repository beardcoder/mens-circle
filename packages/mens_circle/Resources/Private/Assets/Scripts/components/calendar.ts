/**
 * Calendar Composables - Modern Functional Pattern
 * Handles calendar integration for ICS and Google Calendar export
 */

import type { EventData } from '@/Scripts/types';
import { TRACKING_EVENTS, trackEvent } from '@/Scripts/utils/umami';

/**
 * Calendar integration composable
 * Provides calendar export functionality for events
 */
export function useCalendarIntegration(): void {
  const addToCalendarBtn = document.getElementById('addToCalendar');
  const calendarModal = document.getElementById('calendarModal');
  const calendarICS = document.getElementById(
    'calendarICS'
  ) as HTMLAnchorElement;
  const calendarGoogle = document.getElementById(
    'calendarGoogle'
  ) as HTMLAnchorElement;

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

  addToCalendarBtn.addEventListener('click', () => {
    if (!calendarModal) return;

    // Track calendar modal open
    trackEvent(TRACKING_EVENTS.CALENDAR_OPEN, {
      event: eventData.title,
    });

    calendarModal.classList.add('open');

    if (calendarICS) {
      const icsContent = generateICS(eventData);
      const blob = new Blob([icsContent], {
        type: 'text/calendar;charset=utf-8',
      });

      calendarICS.href = URL.createObjectURL(blob);
    }

    if (calendarGoogle) {
      calendarGoogle.href = generateGoogleCalendarUrl(eventData);
    }
  });

  // Track ICS download
  if (calendarICS) {
    calendarICS.addEventListener('click', () => {
      trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_ICS, {
        event: eventData.title,
      });
    });
  }

  // Track Google Calendar click
  if (calendarGoogle) {
    calendarGoogle.addEventListener('click', () => {
      trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_GOOGLE, {
        event: eventData.title,
      });
    });
  }

  if (calendarModal) {
    calendarModal.addEventListener('click', (e) => {
      if (e.target === calendarModal) {
        calendarModal.classList.remove('open');
      }
    });
  }
}

/**
 * Generate ICS file content for calendar import
 */
function generateICS(event: EventData): string {
  const formatDate = (date: string, time: string): string => {
    const d = new Date(`${date}T${time}:00`);

    return d
      .toISOString()
      .replace(/[-:]/g, '')
      .replace(/\.\d{3}/, '');
  };

  const start = formatDate(event.startDate, event.startTime);
  const end = formatDate(event.endDate, event.endTime);
  const now = new Date()
    .toISOString()
    .replace(/[-:]/g, '')
    .replace(/\.\d{3}/, '');

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

/**
 * Generate Google Calendar URL for the event
 */
function generateGoogleCalendarUrl(event: EventData): string {
  const formatGoogleDate = (date: string, time: string): string => {
    return `${date.replace(/-/g, '')}T${time.replace(':', '')}00`;
  };

  const params = new URLSearchParams({
    action: 'TEMPLATE',
    text: event.title,
    dates: `${formatGoogleDate(event.startDate, event.startTime)}/${formatGoogleDate(event.endDate, event.endTime)}`,
    details: event.description,
    location: event.location,
    ctz: 'Europe/Berlin',
  });

  return `https://calendar.google.com/calendar/render?${params.toString()}`;
}
