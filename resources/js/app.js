/**
 * Männerkreis Niederbayern/ Straubing - JavaScript
 * Handles navigation, FAQ accordion, forms, animations, and calendar integration
 */

document.addEventListener('DOMContentLoaded', () => {
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

  // Speichere die Scroll-Position
  let scrollPosition = 0;

  function openNav() {
    scrollPosition = window.pageYOffset;
    nav.classList.add('open');
    navToggle.classList.add('active');
    document.body.classList.add('nav-open');
    document.body.style.top = `-${scrollPosition}px`;
    navToggle.setAttribute('aria-expanded', 'true');
    navToggle.setAttribute('aria-label', 'Menü schließen');
  }

  function closeNav() {
    nav.classList.remove('open');
    navToggle.classList.remove('active');
    document.body.classList.remove('nav-open');
    document.body.style.top = '';
    window.scrollTo({ top: scrollPosition, left: 0, behavior: 'instant' });
    navToggle.setAttribute('aria-expanded', 'false');
    navToggle.setAttribute('aria-label', 'Menü öffnen');
  }

  navToggle.addEventListener('click', () => {
    const isOpen = nav.classList.contains('open');

    if (isOpen) {
      closeNav();
    } else {
      openNav();
    }
  });

  // Close nav when clicking on a link
  const navLinks = nav.querySelectorAll('.nav__link, .nav__cta');

  navLinks.forEach((link) => {
    link.addEventListener('click', () => {
      closeNav();
    });
  });

  // Close nav when clicking outside
  document.addEventListener('click', (e) => {
    if (
      !nav.contains(e.target) &&
      !navToggle.contains(e.target) &&
      nav.classList.contains('open')
    ) {
      closeNav();
    }
  });

  // Close nav on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && nav.classList.contains('open')) {
      closeNav();
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

    question.addEventListener('click', () => {
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

  // Testimonial Form
  const testimonialForm = document.getElementById('testimonialForm');

  if (testimonialForm) {
    // Character counter
    const quoteTextarea = testimonialForm.querySelector('#quote');
    const charCount = document.getElementById('charCount');

    if (quoteTextarea && charCount) {
      quoteTextarea.addEventListener('input', function () {
        charCount.textContent = this.value.length;
      });
    }

    testimonialForm.addEventListener('submit', function (e) {
      e.preventDefault();
      handleTestimonialSubmit(this);
    });
  }
}

function handleNewsletterSubmit(form) {
  const messageContainer = document.getElementById('newsletterMessage');
  const emailInput = form.querySelector('input[type="email"]');
  const email = emailInput.value.trim();
  const submitButton = form.querySelector('button[type="submit"]');

  if (!validateEmail(email)) {
    showMessage(
      messageContainer,
      'Bitte gib eine gültige E-Mail-Adresse ein.',
      'error'
    );

    return;
  }

  // Disable button during submission
  submitButton.disabled = true;
  submitButton.textContent = 'Wird gesendet...';

  // Send to Laravel backend
  fetch(window.routes.newsletter, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': window.routes.csrfToken,
      Accept: 'application/json',
    },
    body: JSON.stringify({ email }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showMessage(messageContainer, data.message, 'success');
        form.reset();
      } else {
        showMessage(
          messageContainer,
          data.message || 'Ein Fehler ist aufgetreten.',
          'error'
        );
      }
    })
    .catch(() => {
      showMessage(
        messageContainer,
        'Ein Fehler ist aufgetreten. Bitte versuche es später erneut.',
        'error'
      );
    })
    .finally(() => {
      submitButton.disabled = false;
      submitButton.textContent = 'Anmelden';
    });
}

function handleRegistrationSubmit(form) {
  const messageContainer = document.getElementById('registrationMessage');
  const formData = new FormData(form);
  const submitButton = form.querySelector('button[type="submit"]');

  const firstName = formData.get('first_name')?.trim();
  const lastName = formData.get('last_name')?.trim();
  const email = formData.get('email')?.trim();
  const phoneNumber = formData.get('phone_number')?.trim() || null;
  const privacy = form.querySelector('input[name="privacy"]')?.checked;
  const eventId = formData.get('event_id');

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

  // Disable button during submission
  submitButton.disabled = true;
  submitButton.textContent = 'Wird gesendet...';

  // Send to Laravel backend
  fetch(window.routes.eventRegister, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': window.routes.csrfToken,
      Accept: 'application/json',
    },
    body: JSON.stringify({
      event_id: eventId,
      first_name: firstName,
      last_name: lastName,
      email,
      phone_number: phoneNumber,
      privacy: privacy ? 1 : 0,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showMessage(messageContainer, data.message, 'success');
        form.reset();
      } else {
        showMessage(
          messageContainer,
          data.message || 'Ein Fehler ist aufgetreten.',
          'error'
        );
      }
    })
    .catch(() => {
      showMessage(
        messageContainer,
        'Ein Fehler ist aufgetreten. Bitte versuche es später erneut.',
        'error'
      );
    })
    .finally(() => {
      submitButton.disabled = false;
      submitButton.textContent = 'Verbindlich anmelden';
    });
}

function handleTestimonialSubmit(form) {
  const messageContainer = document.getElementById('formMessage');
  const formData = new FormData(form);
  const submitButton = form.querySelector('button[type="submit"]');
  const submitText = submitButton.querySelector('.btn__text');
  const submitLoader = submitButton.querySelector('.btn__loader');

  const quote = formData.get('quote')?.trim();
  const authorName = formData.get('author_name')?.trim() || null;
  const role = formData.get('role')?.trim() || null;
  const email = formData.get('email')?.trim();
  const privacy = form.querySelector('input[name="privacy"]')?.checked;

  // Validation
  if (!quote || quote.length < 10) {
    showMessage(
      messageContainer,
      'Bitte teile deine Erfahrung mit uns (mindestens 10 Zeichen).',
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

  // Disable button during submission
  submitButton.disabled = true;

  if (submitText) {
    submitText.style.display = 'none';
  }

  if (submitLoader) {
    submitLoader.style.display = 'inline-block';
  }

  const submitUrl = form.getAttribute('data-submit-url');

  // Send to Laravel backend
  fetch(submitUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content'),
      Accept: 'application/json',
    },
    body: JSON.stringify({
      quote,
      author_name: authorName,
      role,
      email,
      privacy: privacy ? 1 : 0,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showMessage(messageContainer, data.message, 'success');
        form.reset();

        // Reset character counter
        const charCount = document.getElementById('charCount');

        if (charCount) {
          charCount.textContent = '0';
        }
      } else {
        showMessage(
          messageContainer,
          data.message || 'Ein Fehler ist aufgetreten.',
          'error'
        );
      }
    })
    .catch(() => {
      showMessage(
        messageContainer,
        'Ein Fehler ist aufgetreten. Bitte versuche es später erneut.',
        'error'
      );
    })
    .finally(() => {
      submitButton.disabled = false;

      if (submitText) {
        submitText.style.display = 'inline';
      }

      if (submitLoader) {
        submitLoader.style.display = 'none';
      }
    });
}

function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  return re.test(email);
}

function showMessage(container, message, type) {
  if (!container) return;

  container.style.display = 'block';
  container.innerHTML = `<div class="form-message form-message--${type}">${message}</div>`;

  // Auto-hide after 5 seconds
  setTimeout(() => {
    container.innerHTML = '';
    container.style.display = 'none';
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

  // Get event data from window object (set in event.blade.php)
  const eventData = window.eventData || {
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
    if (!calendarModal) {
      return;
    }
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
  });

  // Close modal when clicking outside
  if (calendarModal) {
    calendarModal.addEventListener('click', (e) => {
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

    // Skip if empty anchor or not a valid selector (e.g., blob URLs)
    if (targetId === '#' || !targetId.startsWith('#') || targetId.includes(':'))
      return;

    try {
      const target = document.querySelector(targetId);

      if (target) {
        e.preventDefault();
        const headerHeight =
          document.getElementById('header')?.offsetHeight || 0;
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
    } catch {
      // Invalid selector - ignore silently
    }
  });
});
