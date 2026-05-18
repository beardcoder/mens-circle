/**
 * Service Worker for the Atemübung (Breathing Exercise) standalone PWA.
 * Strategy: cache-first for static assets, network-first for HTML pages.
 */

const CACHE_NAME = 'breathing-app-v1';

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Skip non-same-origin requests
    if (url.origin !== self.location.origin) return;

    // Skip admin and API routes
    if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api')) return;

    const isHtml = event.request.headers.get('accept')?.includes('text/html');

    if (isHtml) {
        // Network-first for HTML: try live, fall back to cache, then 503
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                    return response;
                })
                .catch(() =>
                    caches.match(event.request).then(
                        (cached) =>
                            cached ??
                            new Response('Offline – bitte prüfe deine Internetverbindung.', {
                                status: 503,
                                headers: { 'Content-Type': 'text/plain; charset=utf-8' },
                            })
                    )
                )
        );
    } else {
        // Cache-first for static assets (CSS, JS, fonts, images)
        event.respondWith(
            caches.match(event.request).then((cached) => {
                if (cached) return cached;

                return fetch(event.request).then((response) => {
                    if (!response.ok) return response;

                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                    return response;
                });
            })
        );
    }
});
