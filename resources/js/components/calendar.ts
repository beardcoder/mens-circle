/**
 * Calendar Integration Component
 * Handles ICS and Google Calendar export using stitch-js
 */

import { defineComponent } from '@beardcoder/stitch-js';
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
export const calendarIntegration = defineComponent<CalendarOptions>(
  {
    modalSelector: '#calendarModal',
    icsSelector: '#calendarICS',
    googleSelector: '#calendarGoogle',
  },
  (ctx) => {
    const { options: o } = ctx;
    const calendarModal = document.querySelector<HTMLElement>(o.modalSelector);
    const calendarICS = document.querySelector<HTMLAnchorElement>(
      o.icsSelector
    );
    const calendarGoogle = document.querySelector<HTMLAnchorElement>(
      o.googleSelector
    );

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

    if (calendarICS) {
      const icsContent = generateICS(eventData);
      const blob = new Blob([icsContent], {
        type: 'text/calendar;charset=utf-8',
      });
      const icsBlobUrl = URL.createObjectURL(blob);

      calendarICS.href = icsBlobUrl;
      ctx.onDestroy(() => URL.revokeObjectURL(icsBlobUrl));
    }

    if (calendarGoogle) {
      calendarGoogle.href = generateGoogleCalendarUrl(eventData);
    }

    ctx.on('click', () => {
      if (!calendarModal) return;

      trackEvent(TRACKING_EVENTS.CALENDAR_OPEN, {
        event: eventData.title,
      });

      calendarModal.classList.add('open');
    });

    if (calendarICS) {
      const handleICSClick = (): void => {
        trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_ICS, {
          event: eventData.title,
        });
      };

      calendarICS.addEventListener('click', handleICSClick);
      ctx.onDestroy(() =>
        calendarICS.removeEventListener('click', handleICSClick)
      );
    }

    if (calendarGoogle) {
      const handleGoogleClick = (): void => {
        trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_GOOGLE, {
          event: eventData.title,
        });
      };

      calendarGoogle.addEventListener('click', handleGoogleClick);
      ctx.onDestroy(() =>
        calendarGoogle.removeEventListener('click', handleGoogleClick)
      );
    }

    if (calendarModal) {
      const handleModalClick = (e: MouseEvent): void => {
        if (e.target === calendarModal) {
          calendarModal.classList.remove('open');
        }
      };

      calendarModal.addEventListener('click', handleModalClick);
      ctx.onDestroy(() =>
        calendarModal.removeEventListener('click', handleModalClick)
      );
    }
  }
);
