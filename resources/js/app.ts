/**
 * Männerkreis Niederbayern / Straubing — Application entry point.
 *
 * Built on Alpine.js with the @alpinejs/collapse and @alpinejs/intersect
 * plugins. Each interactive widget is exposed as an Alpine.data() factory or
 * Alpine.directive() so it can be wired up in Blade with `x-data`.
 */

import './types';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';

import { showToast } from '@/utils/toast';
import { validateEmail } from '@/utils/helpers';
import { TRACKING_EVENTS, trackEvent } from '@/utils/umami';
import { initUmamiKit } from '@/utils/umami-kit';
import type { ApiResponse, EventData } from '@/types';

declare global {
  interface Window {
    Alpine: typeof Alpine;
  }
}

Alpine.plugin(collapse);
Alpine.plugin(intersect);

/* ------------------------------------------------------------------ */
/*  Reveal-on-scroll directive: <div x-reveal>...</div>                */
/* ------------------------------------------------------------------ */
Alpine.directive('reveal', (el, { modifiers, expression }) => {
  const once = !modifiers.includes('repeat');
  const threshold = modifiers.includes('full')
    ? 0.9
    : modifiers.includes('half')
      ? 0.5
      : 0.15;

  // Variant: fade | up (default) | down | left | right | zoom
  const variant = modifiers.includes('fade')
    ? ['fade-in']
    : modifiers.includes('zoom')
      ? ['fade-in', 'zoom-in-95']
      : modifiers.includes('left')
        ? ['fade-in', 'slide-in-from-left-8']
        : modifiers.includes('right')
          ? ['fade-in', 'slide-in-from-right-8']
          : modifiers.includes('down')
            ? ['fade-in', 'slide-in-from-top-8']
            : ['fade-in', 'slide-in-from-bottom-8'];

  // Duration: read from `slow` | `fast` | default 700
  const duration = modifiers.includes('slow')
    ? 'duration-1000'
    : modifiers.includes('fast')
      ? 'duration-500'
      : 'duration-700';

  // Delay: pass via expression e.g. `x-reveal="120"` → animation-delay 120ms.
  // We set inline style so we don't depend on Tailwind seeing dynamic class names.
  const delayMs =
    expression && /^\d+$/.test(expression)
      ? Number.parseInt(expression, 10)
      : 0;

  // Initial state — invisible until intersected
  el.classList.add('opacity-0');

  const observer = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        const target = entry.target as HTMLElement;
        if (entry.isIntersecting) {
          target.classList.remove('opacity-0');
          target.classList.add('animate-in', ...variant, duration);
          if (delayMs > 0) target.style.animationDelay = `${delayMs}ms`;
          if (once) observer.unobserve(target);
        } else if (!once) {
          target.classList.add('opacity-0');
          target.classList.remove('animate-in', ...variant, duration);
          target.style.animationDelay = '';
        }
      }
    },
    { threshold, rootMargin: '0px 0px -10% 0px' }
  );

  observer.observe(el);
});

/* ------------------------------------------------------------------ */
/*  Header: scroll state + hero detection                              */
/* ------------------------------------------------------------------ */
Alpine.data('siteHeader', () => ({
  scrolled: false,
  onHero: false,
  navOpen: false,
  navScrollPos: 0,
  toggleNav() {
    if (this.navOpen) this.closeNav();
    else this.openNav();
  },
  openNav() {
    this.navScrollPos = window.scrollY;
    this.navOpen = true;
    document.body.classList.add('overflow-hidden');
  },
  closeNav({ restoreScroll = true }: { restoreScroll?: boolean } = {}) {
    if (!this.navOpen) return;
    this.navOpen = false;
    document.body.classList.remove('overflow-hidden');
    if (restoreScroll) {
      window.scrollTo({ top: this.navScrollPos, behavior: 'instant' });
    }
  },
  onLinkClick() {
    if (this.navOpen) this.closeNav({ restoreScroll: false });
  },
  init() {
    const hero = document.querySelector<HTMLElement>('[data-hero]');

    document.body.classList.toggle('has-hero', Boolean(hero));

    const update = () => {
      this.scrolled = window.scrollY > 50 || !hero;
    };

    update();
    window.addEventListener('scroll', update, { passive: true });

    if (hero) {
      const io = new IntersectionObserver(
        (entries) => {
          for (const entry of entries) {
            this.onHero =
              entry.isIntersecting && entry.intersectionRatio > 0.15;
          }
        },
        {
          threshold: [0, 0.15, 0.35, 0.5],
          rootMargin: '-10% 0px 0px 0px',
        }
      );

      io.observe(hero);
    }
  },
}));

/* ------------------------------------------------------------------ */
/*  Mobile navigation                                                  */
/* ------------------------------------------------------------------ */
Alpine.data('mobileNav', () => ({
  open: false,
  scrollPos: 0,
  toggle() {
    if (this.open) this.close();
    else this.openMenu();
  },
  openMenu() {
    this.scrollPos = window.scrollY;
    this.open = true;
    document.body.classList.add('overflow-hidden');
    document.body.style.top = `-${this.scrollPos}px`;
  },
  close({ restoreScroll = true }: { restoreScroll?: boolean } = {}) {
    if (!this.open) return;
    this.open = false;
    document.body.classList.remove('overflow-hidden');
    document.body.style.top = '';
    if (restoreScroll) {
      window.scrollTo({ top: this.scrollPos, behavior: 'instant' });
    }
  },
  onLinkClick() {
    if (this.open) this.close({ restoreScroll: false });
  },
}));

/* ------------------------------------------------------------------ */
/*  Scroll-to-top button                                               */
/* ------------------------------------------------------------------ */
Alpine.data('scrollToTop', () => ({
  visible: false,
  init() {
    const update = () => {
      this.visible = window.scrollY > 400;
    };

    update();
    window.addEventListener('scroll', update, { passive: true });
  },
  go() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  },
}));

/* ------------------------------------------------------------------ */
/*  Smooth scroll progress bar                                         */
/* ------------------------------------------------------------------ */
Alpine.data('scrollProgress', () => ({
  width: 0,
  init() {
    const root = document.documentElement;
    let target = 0;
    let raf = 0;
    let running = false;

    const compute = () => {
      const max = root.scrollHeight - window.innerHeight;

      target = max > 0 ? Math.min(1, Math.max(0, root.scrollTop / max)) : 0;
    };

    const tick = () => {
      const delta = target - this.width;

      if (Math.abs(delta) < 0.0005) {
        this.width = target;
        running = false;

        return;
      }
      this.width += delta * 0.18;
      raf = requestAnimationFrame(tick);
    };

    const schedule = () => {
      compute();
      if (running) return;
      running = true;
      raf = requestAnimationFrame(tick);
    };

    compute();
    this.width = target;
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule);
    if (typeof ResizeObserver === 'function') {
      new ResizeObserver(schedule).observe(root);
    }

    this.$cleanup = () => cancelAnimationFrame(raf);
  },
  $cleanup: () => {},
}));

/* ------------------------------------------------------------------ */
/*  Generic FAQ accordion item (uses x-collapse)                       */
/* ------------------------------------------------------------------ */
Alpine.data('accordion', (initial = false) => ({
  open: initial,
  toggle() {
    this.open = !this.open;
    if (this.open) {
      trackEvent(TRACKING_EVENTS.FAQ_EXPAND, {
        page: window.location.pathname,
      });
    }
  },
}));

/* ------------------------------------------------------------------ */
/*  Form helpers + abandon tracking                                    */
/* ------------------------------------------------------------------ */
type FieldEl = HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement;

function fieldFilled(field: FieldEl): boolean {
  if (
    field instanceof HTMLInputElement &&
    (field.type === 'checkbox' || field.type === 'radio')
  ) {
    return field.checked;
  }

  return field.value.trim().length > 0;
}

function setupAbandonTracking(
  form: HTMLFormElement,
  eventName: string,
  formType: string
) {
  let submitted = false;
  let tracked = false;
  let firstAt: number | null = null;

  const fields = () =>
    Array.from(
      form.querySelectorAll<FieldEl>('input, textarea, select')
    ).filter(
      (f) =>
        f.name &&
        !f.disabled &&
        !(f instanceof HTMLInputElement && f.type === 'hidden')
    );

  const onInput = () => {
    if (firstAt === null) firstAt = Date.now();
  };
  const onSubmit = () => {
    submitted = true;
  };

  const flush = () => {
    if (tracked || submitted || firstAt === null) return;
    const all = fields();
    const required = all.filter((f) => f.required);

    if (required.length === 0) return;
    const requiredFilled = required.filter(
      (f) => fieldFilled(f) && f.validity.valid
    ).length;

    if (requiredFilled < required.length) return;
    tracked = true;
    trackEvent(eventName, {
      form: formType,
      required_filled: requiredFilled,
      required_total: required.length,
      seconds_since_first_input: Math.round((Date.now() - firstAt) / 1000),
      page: window.location.pathname,
    });
  };

  form.addEventListener('input', onInput);
  form.addEventListener('change', onInput);
  form.addEventListener('submit', onSubmit, { capture: true });
  window.addEventListener('pagehide', flush, { capture: true });
  window.addEventListener('beforeunload', flush, { capture: true });
}

async function postJson(
  url: string,
  body: Record<string, unknown>,
  button: HTMLButtonElement | null
): Promise<ApiResponse> {
  const original = button?.textContent ?? '';

  if (button) {
    button.disabled = true;
    button.textContent = 'Wird gesendet...';
  }
  try {
    const r = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify(body),
    });

    return (await r.json()) as ApiResponse;
  } catch {
    return {
      success: false,
      message: 'Ein Fehler ist aufgetreten. Bitte versuche es erneut.',
    };
  } finally {
    if (button) {
      button.disabled = false;
      button.textContent = original;
    }
  }
}

/* ------------------------------------------------------------------ */
/*  Newsletter form                                                    */
/* ------------------------------------------------------------------ */
Alpine.data('newsletterForm', () => ({
  email: '',
  init() {
    setupAbandonTracking(
      this.$el as HTMLFormElement,
      TRACKING_EVENTS.NEWSLETTER_ABANDON_FILLED,
      'newsletter'
    );
  },
  async submit(e: Event) {
    e.preventDefault();
    const form = this.$el as HTMLFormElement;

    if (!validateEmail(this.email)) {
      showToast('error', 'Bitte gib eine gültige E-Mail-Adresse ein.');

      return;
    }
    trackEvent(TRACKING_EVENTS.NEWSLETTER_SUBMIT);
    const button = form.querySelector<HTMLButtonElement>('[type="submit"]');
    const res = await postJson(
      window.routes.newsletter,
      { email: this.email },
      button
    );

    if (res.success) {
      this.email = '';
      showToast('success', res.message);
      trackEvent(TRACKING_EVENTS.NEWSLETTER_SUCCESS);
    } else {
      showToast('error', res.message);
      trackEvent(TRACKING_EVENTS.NEWSLETTER_ERROR, { error: res.message });
    }
  },
  $el: null as unknown as HTMLElement,
}));

/* ------------------------------------------------------------------ */
/*  Event registration form                                            */
/* ------------------------------------------------------------------ */
Alpine.data('registrationForm', (eventId: number | string) => ({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  privacy: false,
  init() {
    setupAbandonTracking(
      this.$el as HTMLFormElement,
      TRACKING_EVENTS.EVENT_REGISTRATION_ABANDON_FILLED,
      'event-registration'
    );
  },
  async submit(e: Event) {
    e.preventDefault();
    const form = this.$el as HTMLFormElement;

    if (!this.firstName.trim() || !this.lastName.trim()) {
      showToast('error', 'Bitte fülle alle Pflichtfelder aus.');

      return;
    }
    if (!validateEmail(this.email)) {
      showToast('error', 'Bitte gib eine gültige E-Mail-Adresse ein.');

      return;
    }
    if (!this.privacy) {
      showToast('error', 'Bitte bestätige die Datenschutzerklärung.');

      return;
    }
    trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_SUBMIT, {
      event_id: String(eventId),
      has_phone: this.phone ? 'yes' : 'no',
    });
    const button = form.querySelector<HTMLButtonElement>('[type="submit"]');
    const res = await postJson(
      window.routes.eventRegister,
      {
        event_id: eventId,
        first_name: this.firstName.trim(),
        last_name: this.lastName.trim(),
        email: this.email.trim(),
        phone_number: this.phone.trim() || null,
        privacy: 1,
      },
      button
    );

    if (res.success) {
      this.firstName = this.lastName = this.email = this.phone = '';
      this.privacy = false;
      showToast('success', res.message);
      trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_SUCCESS);
    } else {
      showToast('error', res.message);
      trackEvent(TRACKING_EVENTS.EVENT_REGISTRATION_ERROR, {
        error: res.message,
      });
    }
  },
  $el: null as unknown as HTMLElement,
}));

/* ------------------------------------------------------------------ */
/*  Testimonial form                                                   */
/* ------------------------------------------------------------------ */
Alpine.data('testimonialForm', (submitUrl: string) => ({
  quote: '',
  authorName: '',
  role: '',
  email: '',
  privacy: false,
  init() {
    setupAbandonTracking(
      this.$el as HTMLFormElement,
      TRACKING_EVENTS.TESTIMONIAL_ABANDON_FILLED,
      'testimonial'
    );
  },
  get charCount(): number {
    return this.quote.length;
  },
  async submit(e: Event) {
    e.preventDefault();
    const form = this.$el as HTMLFormElement;

    if (this.quote.trim().length < 10) {
      showToast(
        'error',
        'Bitte teile deine Erfahrung mit uns (mindestens 10 Zeichen).'
      );

      return;
    }
    if (!validateEmail(this.email)) {
      showToast('error', 'Bitte gib eine gültige E-Mail-Adresse ein.');

      return;
    }
    if (!this.privacy) {
      showToast('error', 'Bitte bestätige die Datenschutzerklärung.');

      return;
    }
    trackEvent(TRACKING_EVENTS.TESTIMONIAL_SUBMIT, {
      has_name: this.authorName ? 'yes' : 'no',
      has_role: this.role ? 'yes' : 'no',
      char_count: this.quote.length,
    });
    const button = form.querySelector<HTMLButtonElement>('[type="submit"]');
    const res = await postJson(
      submitUrl,
      {
        quote: this.quote.trim(),
        author_name: this.authorName.trim() || null,
        role: this.role.trim() || null,
        email: this.email.trim(),
        privacy: 1,
      },
      button
    );

    if (res.success) {
      this.quote = this.authorName = this.role = this.email = '';
      this.privacy = false;
      showToast('success', res.message);
      trackEvent(TRACKING_EVENTS.TESTIMONIAL_SUCCESS);
    } else {
      showToast('error', res.message);
      trackEvent(TRACKING_EVENTS.TESTIMONIAL_ERROR, { error: res.message });
    }
  },
  $el: null as unknown as HTMLElement,
}));

/* ------------------------------------------------------------------ */
/*  Calendar export modal                                              */
/* ------------------------------------------------------------------ */
function formatICSDate(date: string, time: string): string {
  return new Date(`${date}T${time}:00`)
    .toISOString()
    .replace(/[-:]/g, '')
    .replace(/\.\d{3}/, '');
}

function buildICS(ev: EventData): string {
  const start = formatICSDate(ev.startDate, ev.startTime);
  const end = formatICSDate(ev.endDate, ev.endTime);
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
    `SUMMARY:${ev.title}`,
    `DESCRIPTION:${ev.description.replace(/\n/g, '\\n')}`,
    `LOCATION:${ev.location}`,
    'STATUS:CONFIRMED',
    'END:VEVENT',
    'END:VCALENDAR',
  ].join('\n');
}

function buildGoogleUrl(ev: EventData): string {
  const fmt = (d: string, t: string) =>
    `${d.replace(/-/g, '')}T${t.replace(':', '')}00`;
  const params = new URLSearchParams({
    action: 'TEMPLATE',
    text: ev.title,
    dates: `${fmt(ev.startDate, ev.startTime)}/${fmt(ev.endDate, ev.endTime)}`,
    details: ev.description,
    location: ev.location,
    ctz: 'Europe/Berlin',
  });

  return `https://calendar.google.com/calendar/render?${params.toString()}`;
}

Alpine.data('calendar', () => ({
  show: false,
  icsUrl: '',
  googleUrl: '',
  eventTitle: '',
  init() {
    const el = this.$el as HTMLElement;
    const ds = el.dataset;
    const ev: EventData = {
      title: ds.eventTitle ?? '',
      description: ds.eventDescription ?? '',
      location: ds.eventLocation ?? '',
      startDate: ds.eventStartDate ?? '',
      startTime: ds.eventStartTime ?? '',
      endDate: ds.eventEndDate ?? '',
      endTime: ds.eventEndTime ?? '',
    };

    this.eventTitle = ev.title;
    if (ev.startDate) {
      this.icsUrl = URL.createObjectURL(
        new Blob([buildICS(ev)], { type: 'text/calendar;charset=utf-8' })
      );
      this.googleUrl = buildGoogleUrl(ev);
    }
  },
  open() {
    this.show = true;
    trackEvent(TRACKING_EVENTS.CALENDAR_OPEN, { event: this.eventTitle });
  },
  close() {
    this.show = false;
  },
  trackIcs() {
    trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_ICS, {
      event: this.eventTitle,
    });
  },
  trackGoogle() {
    trackEvent(TRACKING_EVENTS.CALENDAR_DOWNLOAD_GOOGLE, {
      event: this.eventTitle,
    });
  },
  $el: null as unknown as HTMLElement,
}));

/* ------------------------------------------------------------------ */
/*  Event location map (Leaflet, lazy)                                 */
/* ------------------------------------------------------------------ */
Alpine.data('eventMap', () => ({
  loaded: false,
  init() {
    const el = this.$el as HTMLElement;
    const lat = Number.parseFloat(el.dataset.lat ?? '');
    const lng = Number.parseFloat(el.dataset.lng ?? '');

    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
      el.hidden = true;

      return;
    }
    const observer = new IntersectionObserver(
      (entries, obs) => {
        for (const entry of entries) {
          if (entry.isIntersecting) {
            obs.disconnect();
            void this.load(lat, lng);
            break;
          }
        }
      },
      { rootMargin: '200px' }
    );

    observer.observe(el);
  },
  async load(lat: number, lng: number) {
    if (this.loaded) return;
    this.loaded = true;
    const el = this.$el as HTMLElement;
    const [{ default: L }] = await Promise.all([
      import('leaflet'),
      import('leaflet/dist/leaflet.css'),
    ]);
    const container = el.querySelector<HTMLElement>('[data-map-canvas]');

    if (!container) return;

    const map = L.map(container, { scrollWheelZoom: false }).setView(
      [lat, lng],
      16
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    const title = el.dataset.title ?? '';
    const address = el.dataset.address ?? '';
    const isCoarse =
      typeof window.matchMedia === 'function' &&
      window.matchMedia('(pointer: coarse)').matches;
    const directionsUrl = isCoarse
      ? `geo:${lat},${lng}?q=${lat},${lng}(${encodeURIComponent(address || title)})`
      : `https://www.openstreetmap.org/directions?to=${lat}%2C${lng}`;

    const escape = (s: string) =>
      s
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    const icon = L.divIcon({
      className: 'leaflet-pin',
      html: `<svg viewBox="0 0 32 44" class="w-8 h-11 fill-[var(--color-terracotta)]" aria-hidden="true">
        <path d="M16 0C7.2 0 0 7 0 15.5 0 27 16 44 16 44s16-17 16-28.5C32 7 24.8 0 16 0z"/>
        <circle cx="16" cy="15.5" r="6" fill="#fff"/></svg>`,
      iconSize: [32, 44],
      iconAnchor: [16, 44],
      popupAnchor: [0, -40],
    });

    L.marker([lat, lng], { icon })
      .addTo(map)
      .bindPopup(
        `<strong>${escape(title)}</strong>${address ? '<br>' + escape(address) : ''}` +
          `<br><a class="underline" href="${directionsUrl}" target="_blank" rel="noopener">Route planen</a>`
      );

    container.addEventListener('click', () => map.scrollWheelZoom.enable());
    container.addEventListener('mouseleave', () =>
      map.scrollWheelZoom.disable()
    );
  },
  $el: null as unknown as HTMLElement,
}));

/* ------------------------------------------------------------------ */
/*  Breathing exercise                                                 */
/* ------------------------------------------------------------------ */
type BreathingPhase =
  | 'idle'
  | 'breathing'
  | 'retention'
  | 'recovery'
  | 'complete';

const PHASE_LABEL: Record<BreathingPhase, string> = {
  idle: 'Bereit',
  breathing: 'Atmen',
  retention: 'Halten',
  recovery: 'Erholung',
  complete: 'Geschafft',
};

const formatTime = (s: number) => {
  const m = Math.floor(s / 60);
  const r = Math.floor(s % 60);

  return `${String(m).padStart(2, '0')}:${String(r).padStart(2, '0')}`;
};

Alpine.data('breathing', () => ({
  breaths: 30,
  rounds: 3,
  recoveryHold: 15,
  cycleMs: 3600,
  phase: 'idle' as BreathingPhase,
  round: 0,
  breath: 0,
  counter: '',
  timer: '00:00',
  timerHandle: 0,
  rafHandle: 0,
  breathHandle: 0,
  startedAt: 0,
  retStart: 0,
  init() {
    this.counter = `${this.rounds} Runden · ${this.breaths} Atemzüge`;
    (this.$el as HTMLElement).style.setProperty(
      '--breathing-cycle-ms',
      `${this.cycleMs}ms`
    );
  },
  get phaseLabel() {
    return PHASE_LABEL[this.phase];
  },
  get isIdle() {
    return this.phase === 'idle' || this.phase === 'complete';
  },
  get isHolding() {
    return this.phase === 'retention' || this.phase === 'recovery';
  },
  setBreaths(v: number) {
    if (!this.isIdle) return;
    this.breaths = Math.max(10, Math.min(60, v));
    this.counter = `${this.rounds} Runden · ${this.breaths} Atemzüge`;
  },
  stepRounds(d: number) {
    if (!this.isIdle) return;
    this.rounds = Math.max(1, Math.min(10, this.rounds + d));
    this.counter = `${this.rounds} Runden · ${this.breaths} Atemzüge`;
  },
  stepRecovery(d: number) {
    if (!this.isIdle) return;
    this.recoveryHold = Math.max(5, Math.min(60, this.recoveryHold + d));
  },
  clearAll() {
    if (this.timerHandle) window.clearTimeout(this.timerHandle);
    if (this.rafHandle) window.clearTimeout(this.rafHandle);
    if (this.breathHandle) window.clearInterval(this.breathHandle);
    this.timerHandle = this.rafHandle = this.breathHandle = 0;
  },
  reset() {
    this.clearAll();
    this.phase = 'idle';
    this.round = this.breath = 0;
    this.counter = `${this.rounds} Runden · ${this.breaths} Atemzüge`;
    this.timer = '00:00';
  },
  start() {
    this.round = this.breath = 0;
    this.timer = '00:00';
    this.startBreathing();
  },
  startBreathing() {
    this.phase = 'breathing';
    this.round += 1;
    this.breath = 1;
    this.counter = `Atemzug ${this.breath}`;
    this.startedAt = performance.now();
    this.breathHandle = window.setInterval(() => {
      if (this.phase !== 'breathing') return;
      this.breath += 1;
      if (this.breath > this.breaths) {
        this.startRetention();

        return;
      }
      this.counter = `Atemzug ${this.breath}`;
    }, this.cycleMs);
    const tick = () => {
      if (this.phase !== 'breathing') return;
      this.timer = formatTime(
        Math.floor((performance.now() - this.startedAt) / 1000)
      );
      this.rafHandle = window.setTimeout(tick, 1000);
    };

    this.rafHandle = window.setTimeout(tick, 1000);
  },
  startRetention() {
    this.clearAll();
    this.phase = 'retention';
    this.retStart = performance.now();
    const tick = () => {
      const elapsed = Math.floor((performance.now() - this.retStart) / 1000);

      this.counter = `Halten · ${formatTime(elapsed)}`;
      this.timer = formatTime(elapsed);
      this.rafHandle = window.setTimeout(tick, 250);
    };

    tick();
  },
  startRecovery() {
    this.clearAll();
    this.phase = 'recovery';
    let remaining = this.recoveryHold;

    this.counter = `Halten · ${remaining}s`;
    this.timer = formatTime(remaining);
    const tick = () => {
      remaining -= 1;
      if (remaining <= 0) {
        if (this.round >= this.rounds) {
          this.finish();

          return;
        }
        this.startBreathing();

        return;
      }
      this.counter = `Halten · ${remaining}s`;
      this.timer = formatTime(remaining);
      this.timerHandle = window.setTimeout(tick, 1000);
    };

    this.timerHandle = window.setTimeout(tick, 1000);
  },
  releaseHold() {
    if (this.phase === 'retention') this.startRecovery();
    else if (this.phase === 'recovery') {
      this.clearAll();
      if (this.round >= this.rounds) this.finish();
      else this.startBreathing();
    }
  },
  finish() {
    this.clearAll();
    this.phase = 'complete';
    this.counter = 'Nimm dir einen Moment, spüre nach.';
  },
  $el: null as unknown as HTMLElement,
}));

/* ------------------------------------------------------------------ */
/*  Boot                                                               */
/* ------------------------------------------------------------------ */
window.Alpine = Alpine;
Alpine.start();

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => initUmamiKit(), {
    once: true,
  });
} else {
  initUmamiKit();
}
