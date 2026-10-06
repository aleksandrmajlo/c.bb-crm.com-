const CACHE_NAME = 'bb-crm-pwa-v1';
const OFFLINE_URL = '/pwa/offline.html';

const APP_PATHS = ['/atmosphera', '/utc'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.add(OFFLINE_URL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('bb-crm-pwa-') && key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.mode !== 'navigate') {
        return;
    }

    const url = new URL(event.request.url);
    const isClubPage = url.origin === self.location.origin
        && APP_PATHS.some((path) => url.pathname.startsWith(path));

    if (!isClubPage) {
        return;
    }

    event.respondWith(
        fetch(event.request).catch(() => caches.match(OFFLINE_URL))
    );
});
