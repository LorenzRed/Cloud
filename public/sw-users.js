/* Stale-while-revalidate for /users GET pages */
const VERSION = 'v1';

function cacheNameFromScript() {
    const uid = new URL(self.location.href).searchParams.get('uid') || 'anon';
    return `users-swr-${VERSION}-${uid}`;
}

function shouldHandle(url) {
    if (url.origin !== self.location.origin) {
        return false;
    }

    const path = url.pathname.replace(/\/+$/, '') || '/';
    if (path !== '/users' && !path.startsWith('/users/')) {
        return false;
    }

    const blocked = [
        '/users/verification',
        '/users/profile/bhw-chat',
        '/users/profile/chat-support',
        '/users/session/csrf',
    ];

    return !blocked.some((prefix) => path === prefix || path.startsWith(`${prefix}/`));
}

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(Promise.resolve());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const current = cacheNameFromScript();
            const keys = await caches.keys();
            await Promise.all(
                keys
                    .filter((key) => key.startsWith('users-swr-') && key !== current)
                    .map((key) => caches.delete(key))
            );
            await self.clients.claim();
        })()
    );
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'CLEAR_USERS_SWR') {
        event.waitUntil(
            caches.keys().then((keys) =>
                Promise.all(keys.filter((key) => key.startsWith('users-swr-')).map((key) => caches.delete(key)))
            )
        );
    }
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    if (request.headers.get('X-Users-SWR') === 'revalidate') {
        return;
    }

    const url = new URL(request.url);
    if (!shouldHandle(url)) {
        return;
    }

    const cacheName = cacheNameFromScript();

    event.respondWith(
        (async () => {
            const cache = await caches.open(cacheName);
            const cached = await cache.match(request);

            const networkPromise = fetch(request)
                .then(async (response) => {
                    if (response && response.ok) {
                        await cache.put(request, response.clone());
                        const windows = await self.clients.matchAll({
                            type: 'window',
                            includeUncontrolled: true,
                        });
                        windows.forEach((client) => {
                            client.postMessage({
                                type: 'users-swr-updated',
                                url: request.url,
                            });
                        });
                    }
                    return response;
                })
                .catch(() => cached || null);

            if (cached) {
                event.waitUntil(networkPromise);
                return cached;
            }

            const fresh = await networkPromise;
            return fresh || new Response('Offline', { status: 503, statusText: 'Offline' });
        })()
    );
});
