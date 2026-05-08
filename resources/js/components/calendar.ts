/**
 * Calendar Integration Component
 * Handles ICS and Google Calendar export
 */

import type { EventData } from '@/types';
import { TRACKING_EVENTS, trackEvent } from '@/utils/umami';

interface CalendarOptions {
  modalSelector: string;
  icsSelector: string;
  googleSelector: string;
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

/**
 * Calendar integration component
 * Attach to #addToCalendar — manages calendar export modal and download links
 */
export function calendarIntegration(
  options: CalendarOptions = {
    modalSelector: '#calendarModal',
    icsSelector: '#calendarICS',
    googleSelector: '#calendarGoogle',
  }
) {
  return (el: HTMLElement) => {
    const calendarModal = document.querySelector<HTMLElement>(
      options.modalSelector
    );
    const calendarICS = document.querySelector<HTMLAnchorElement>(
      options.icsSelector
    );
    const calendarGoogle = document.querySelector<HTMLAnchorElement>(
      options.googleSelector
    );

    const fallback: EventData = {
      title: 'Männerkreis Niederbayern/ Straubing',
      description:
        'Treffen des Männerkreis Niederbayern/ Straubing. Ein Raum für echte Begegnung unter Männern.',
      location: 'Straubing (genaue Adresse nach Anmeldung)',
      startDate: '2025-01-24',
      startTime: '19:00',
      endDate: '2025-01-24',
      endTime: '21:30',
    };

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
      : (window.eventData ?? fallback);

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

    const handleButtonClick = (): void => {
      if (!calendarModal) return;

      trackEvent(TRACKING_EVENTS.CALENDAR_OPEN, {
        event: eventData.title,
      });

      calendarModal.classList.add('open');
    };

    const handleICSClick = (): void => {
      trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_ICS, {
        event: eventData.title,
      });
    };

    const handleGoogleClick = (): void => {
      trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_GOOGLE, {
        event: eventData.title,
      });
    };

    const handleModalClick = (e: MouseEvent): void => {
      if (e.target === calendarModal && calendarModal) {
        calendarModal.classList.remove('open');
      }
    };

    el.addEventListener('click', handleButtonClick);
    calendarICS?.addEventListener('click', handleICSClick);
    calendarGoogle?.addEventListener('click', handleGoogleClick);
    calendarModal?.addEventListener('click', handleModalClick);

    // Cleanup function (not currently called, but documents cleanup logic)
    return () => {
      el.removeEventListener('click', handleButtonClick);
      calendarICS?.removeEventListener('click', handleICSClick);
      calendarGoogle?.removeEventListener('click', handleGoogleClick);
      calendarModal?.removeEventListener('click', handleModalClick);
      if (icsBlobUrl) {
        URL.revokeObjectURL(icsBlobUrl);
      }
    };
  };
}
