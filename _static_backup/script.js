/**
 * Männerkreis Straubing - JavaScript
 * Handles navigation, FAQ accordion, forms, animations, and calendar integration
 */

document.addEventListener('DOMContentLoaded', function () {
  // Initialize all components
  initNavigation();
  initScrollHeader();
  initFAQ();
  initForms();
  initScrollAnimations();
  initCalendarIntegration();
});

/**
 * Mobile Navigation Toggle
 */
function initNavigation() {
  const navToggle = document.getElementById('navToggle');
  const nav = document.getElementById('nav');

  if (!navToggle || !nav) return;

  navToggle.addEventListener('click', function () {
    nav.classList.toggle('open');
    navToggle.classList.toggle('active');

    // Update ARIA
    const isOpen = nav.classList.contains('open');

    navToggle.setAttribute('aria-expanded', isOpen);
    navToggle.setAttribute(
      'aria-label',
      isOpen ? 'Menü schließen' : 'Menü öffnen'
    );
  });

  // Close nav when clicking on a link
  const navLinks = nav.querySelectorAll('.nav__link, .nav__cta');

  navLinks.forEach((link) => {
    link.addEventListener('click', function () {
      nav.classList.remove('open');
      navToggle.classList.remove('active');
    });
  });

  // Close nav when clicking outside
  document.addEventListener('click', function (e) {
    if (!nav.contains(e.target) && !navToggle.contains(e.target)) {
      nav.classList.remove('open');
      navToggle.classList.remove('active');
    }
  });
}

/**
 * Header scroll effect
 */
function initScrollHeader() {
  const header = document.getElementById('header');

  if (!header) return;

  window.addEventListener(
    'scroll',
    () => {
      const currentScroll = window.pageYOffset;

      if (currentScroll > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    },
    { passive: true }
  );
}

/**
 * FAQ Accordion
 */
function initFAQ() {
  const faqItems = document.querySelectorAll('.faq-item');

  faqItems.forEach((item) => {
    const question = item.querySelector('.faq-item__question');

    if (!question) return;

    question.addEventListener('click', function () {
      const isActive = item.classList.contains('active');

      // Close all other items
      faqItems.forEach((otherItem) => {
        if (otherItem !== item) {
          otherItem.classList.remove('active');
          otherItem
            .querySelector('.faq-item__question')
            .setAttribute('aria-expanded', 'false');
        }
      });

      // Toggle current item
      item.classList.toggle('active');
      question.setAttribute('aria-expanded', !isActive);
    });
  });
}

/**
 * Form Handling
 */
function initForms() {
  // Newsletter Form
  const newsletterForm = document.getElementById('newsletterForm');

  if (newsletterForm) {
    newsletterForm.addEventListener('submit', function (e) {
      e.preventDefault();
      handleNewsletterSubmit(this);
    });
  }

  // Registration Form
  const registrationForm = document.getElementById('registrationForm');

  if (registrationForm) {
    registrationForm.addEventListener('submit', function (e) {
      e.preventDefault();
      handleRegistrationSubmit(this);
    });
  }
}

function handleNewsletterSubmit(form) {
  const messageContainer = document.getElementById('newsletterMessage');
  const emailInput = form.querySelector('input[type="email"]');
  const email = emailInput.value.trim();

  if (!validateEmail(email)) {
    showMessage(
      messageContainer,
      'Bitte gib eine gültige E-Mail-Adresse ein.',
      'error'
    );

    return;
  }

  // Simulate successful submission
  // In production, this would be an actual API call
  showMessage(
    messageContainer,
    'Vielen Dank! Du wurdest erfolgreich für den Newsletter angemeldet.',
    'success'
  );
  form.reset();

  // Optional: Send to backend
  // sendToBackend('/api/newsletter', { email });
}

function handleRegistrationSubmit(form) {
  const messageContainer = document.getElementById('registrationMessage');
  const formData = new FormData(form);

  const firstName = formData.get('firstName')?.trim();
  const lastName = formData.get('lastName')?.trim();
  const email = formData.get('email')?.trim();
  const privacy = form.querySelector('input[name="privacy"]')?.checked;

  // Validation
  if (!firstName || !lastName) {
    showMessage(
      messageContainer,
      'Bitte fülle alle Pflichtfelder aus.',
      'error'
    );

    return;
  }

  if (!validateEmail(email)) {
    showMessage(
      messageContainer,
      'Bitte gib eine gültige E-Mail-Adresse ein.',
      'error'
    );

    return;
  }

  if (!privacy) {
    showMessage(
      messageContainer,
      'Bitte bestätige die Datenschutzerklärung.',
      'error'
    );

    return;
  }

  // Simulate successful submission
  showMessage(
    messageContainer,
    `Vielen Dank, ${firstName}! Deine Anmeldung war erfolgreich. Du erhältst in Kürze eine Bestätigung per E-Mail.`,
    'success'
  );
  form.reset();

  // Optional: Send to backend
  // sendToBackend('/api/registration', { firstName, lastName, email });
}

function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  return re.test(email);
}

function showMessage(container, message, type) {
  if (!container) return;

  container.innerHTML = `<div class="form-message form-message--${type}">${message}</div>`;

  // Auto-hide after 5 seconds
  setTimeout(() => {
    container.innerHTML = '';
  }, 5000);
}

/**
 * Scroll Animations
 */
function initScrollAnimations() {
  const fadeElements = document.querySelectorAll('.fade-in');
  const staggerElements = document.querySelectorAll('.stagger-children');

  const allAnimatedElements = [...fadeElements, ...staggerElements];

  if (!allAnimatedElements.length) return;

  // Check if IntersectionObserver is supported
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px',
      }
    );

    allAnimatedElements.forEach((el) => observer.observe(el));
  } else {
    // Fallback: show all elements immediately
    allAnimatedElements.forEach((el) => el.classList.add('visible'));
  }
}

/**
 * Calendar Integration (Add to Calendar)
 */
function initCalendarIntegration() {
  const addToCalendarBtn = document.getElementById('addToCalendar');
  const calendarModal = document.getElementById('calendarModal');
  const calendarICS = document.getElementById('calendarICS');
  const calendarGoogle = document.getElementById('calendarGoogle');

  if (!addToCalendarBtn) return;

  // Event data - update these values for each event
  const eventData = {
    title: 'Männerkreis Straubing',
    description:
      'Treffen des Männerkreis Straubing. Ein Raum für echte Begegnung unter Männern.',
    location: 'Straubing (genaue Adresse nach Anmeldung)',
    startDate: '2025-01-24',
    startTime: '19:00',
    endDate: '2025-01-24',
    endTime: '21:30',
  };

  addToCalendarBtn.addEventListener('click', function () {
    if (calendarModal) {
      calendarModal.classList.add('open');

      // Generate ICS file
      if (calendarICS) {
        const icsContent = generateICS(eventData);
        const blob = new Blob([icsContent], {
          type: 'text/calendar;charset=utf-8',
        });

        calendarICS.href = URL.createObjectURL(blob);
      }

      // Generate Google Calendar link
      if (calendarGoogle) {
        calendarGoogle.href = generateGoogleCalendarUrl(eventData);
      }
    }
  });

  // Close modal when clicking outside
  if (calendarModal) {
    calendarModal.addEventListener('click', function (e) {
      if (e.target === calendarModal) {
        calendarModal.classList.remove('open');
      }
    });
  }
}

function generateICS(event) {
  const formatDate = (date, time) => {
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
PRODID:-//Männerkreis Straubing//DE
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

function generateGoogleCalendarUrl(event) {
  const formatGoogleDate = (date, time) => {
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

/**
 * Smooth scroll for anchor links
 */
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener('click', function (e) {
    const targetId = this.getAttribute('href');

    if (targetId === '#') return;

    const target = document.querySelector(targetId);

    if (target) {
      e.preventDefault();
      const headerHeight = document.getElementById('header')?.offsetHeight || 0;
      const targetPosition =
        target.getBoundingClientRect().top +
        window.pageYOffset -
        headerHeight -
        20;

      window.scrollTo({
        top: targetPosition,
        behavior: 'smooth',
      });
    }
  });
});
