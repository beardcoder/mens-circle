// Progressive enhancement for the public site: mobile nav + async forms.
(function () {
    'use strict';

    const routes = window.routes || {};

    // --- Mobile navigation toggle ---
    const header = document.querySelector('[data-lume="site-header"]');
    if (header) {
        const toggle = header.querySelector('[data-lume-part="toggle"]');
        const nav = header.querySelector('[data-lume-part="nav"]');
        if (toggle && nav) {
            toggle.addEventListener('click', function () {
                const open = nav.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', String(open));
            });
        }
    }

    function setMessage(el, text, kind) {
        if (!el) return;
        el.textContent = text;
        el.classList.remove('is-success', 'is-error');
        el.classList.add(kind === 'error' ? 'is-error' : 'is-success');
    }

    async function postJSON(url, payload) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        let data = {};
        try { data = await res.json(); } catch (_) { /* ignore */ }
        return { ok: res.ok, data };
    }

    function bindForm(selector, buildPayload, urlKey, messageId) {
        const form = document.querySelector(selector);
        if (!form) return;
        const message = messageId ? document.getElementById(messageId) : null;
        const button = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const url = routes[urlKey];
            if (!url) return;
            const payload = buildPayload(new FormData(form));
            if (button) button.disabled = true;
            try {
                const { ok, data } = await postJSON(url, payload);
                setMessage(message, data.message || (ok ? 'Erfolgreich.' : 'Es ist ein Fehler aufgetreten.'), ok ? 'success' : 'error');
                if (ok) form.reset();
            } catch (_) {
                setMessage(message, 'Verbindung fehlgeschlagen. Bitte versuche es später erneut.', 'error');
            } finally {
                if (button) button.disabled = false;
            }
        });
    }

    bindForm('[data-lume="newsletter-form"]', (fd) => ({ email: fd.get('email') }), 'newsletter', 'newsletterMessage');

    bindForm('[data-lume="event-register-form"]', (fd) => ({
        event_id: Number(fd.get('event_id')),
        first_name: fd.get('first_name'),
        last_name: fd.get('last_name'),
        email: fd.get('email'),
        phone_number: fd.get('phone_number') || '',
        privacy: fd.get('privacy') === '1',
    }), 'eventRegister', 'eventRegisterMessage');

    bindForm('[data-lume="testimonial-form"]', (fd) => ({
        quote: fd.get('quote'),
        author_name: fd.get('author_name') || '',
        role: fd.get('role') || '',
        email: fd.get('email'),
        privacy: fd.get('privacy') === '1',
    }), 'testimonial', 'testimonialMessage');
})();
