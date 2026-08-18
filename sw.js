/**
 * Findownn Service Worker — offline-safe static caching
 */
const CACHE_NAME = 'findownn-v2';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll([
            './offline.html',
        ])).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;

    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    // Cache static assets (css, js, images, manifest)
    const isStatic = /\.(css|js|png|jpg|jpeg|webp|svg|ico|woff2?)$/i.test(url.pathname)
        || url.pathname.endsWith('/manifest.json');

    if (isStatic) {
        const isJs = /\.js$/i.test(url.pathname);

        // JS: network-first so API client updates deploy without stale SW cache
        if (isJs) {
            event.respondWith(
                fetch(req).then((response) => {
                    if (response.ok) {
                        caches.open(CACHE_NAME).then((cache) => cache.put(req, response.clone()));
                    }
                    return response;
                }).catch(() => caches.match(req))
            );
            return;
        }

        event.respondWith(
            caches.open(CACHE_NAME).then(async (cache) => {
                const cached = await cache.match(req);
                if (cached) return cached;

                try {
                    const response = await fetch(req);
                    if (response.ok) cache.put(req, response.clone());
                    return response;
                } catch (_) {
                    return cached || Response.error();
                }
            })
        );
        return;
    }

    // Navigation: network first, fallback to offline page
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).catch(() =>
                caches.match('./offline.html').then((r) => r || caches.match('/offline.html'))
            )
        );
    }
});
