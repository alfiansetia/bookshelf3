/*
 * Service worker Rak Buku.
 *
 * Strategi:
 * - Navigasi memakai network-first supaya HTML selalu yang terbaru, lalu
 *   jatuh ke salinan cache dan terakhir ke halaman offline.
 * - Aset statis (build, ikon, gambar, font) memakai stale-while-revalidate.
 * - Permintaan Inertia (XHR) dan area yang butuh login tidak pernah di-cache.
 */

const VERSION = 'v2';
const STATIC_CACHE = `rak-buku-static-${VERSION}`;
const PAGE_CACHE = `rak-buku-pages-${VERSION}`;
const OFFLINE_URL = '/offline.html';
const MAX_STATIC_ENTRIES = 80;
const MAX_PAGE_ENTRIES = 20;

const PRECACHE_URLS = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/favicon.svg',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

/** Area yang butuh login dan tidak boleh disimpan di cache. */
const PRIVATE_PREFIXES = ['/dashboard', '/books', '/settings', '/onboarding'];

const STATIC_PATTERN =
    /\.(?:css|js|mjs|woff2?|ttf|otf|png|jpe?g|svg|webp|avif|ico)$/;

self.addEventListener('install', (event) => {
    event.waitUntil(
        (async () => {
            const cache = await caches.open(STATIC_CACHE);

            await Promise.all(
                PRECACHE_URLS.map((url) =>
                    cache.add(new Request(url, { cache: 'reload' })).catch(() => {}),
                ),
            );

            await self.skipWaiting();
        })(),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const keys = await caches.keys();

            await Promise.all(
                keys
                    .filter((key) => key !== STATIC_CACHE && key !== PAGE_CACHE)
                    .map((key) => caches.delete(key)),
            );

            await self.clients.claim();
        })(),
    );
});

self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') {
        void self.skipWaiting();
    }
});

async function trimCache(cacheName, maxEntries) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();

    if (keys.length <= maxEntries) {
        return;
    }

    await Promise.all(
        keys.slice(0, keys.length - maxEntries).map((key) => cache.delete(key)),
    );
}

function isCacheablePath(pathname) {
    return !PRIVATE_PREFIXES.some(
        (prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`),
    );
}

async function cacheFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    if (cached) {
        return cached;
    }

    const response = await fetch(request);

    if (response.ok) {
        await cache.put(request, response.clone());
    }

    return response;
}

async function staleWhileRevalidate(request, cacheName, maxEntries) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    const network = fetch(request)
        .then(async (response) => {
            if (response.ok) {
                await cache.put(request, response.clone());
                void trimCache(cacheName, maxEntries);
            }

            return response;
        })
        .catch(() => null);

    if (cached) {
        return cached;
    }

    const response = await network;

    if (response) {
        return response;
    }

    return Response.error();
}

async function handleNavigation(request) {
    try {
        const response = await fetch(request);

        if (response.ok && isCacheablePath(new URL(request.url).pathname)) {
            const cache = await caches.open(PAGE_CACHE);

            await cache.put(request, response.clone());
            void trimCache(PAGE_CACHE, MAX_PAGE_ENTRIES);
        }

        return response;
    } catch (error) {
        const cached = await caches.match(request, { cacheName: PAGE_CACHE });

        if (cached) {
            return cached;
        }

        const offline = await cacheFirst(
            new Request(OFFLINE_URL, { cache: 'reload' }),
            STATIC_CACHE,
        );

        return offline;
    }
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    // Jangan pernah menyimpan service worker itu sendiri.
    if (url.pathname === '/sw.js') {
        return;
    }

    // Biarkan Inertia mengambil data langsung dari jaringan.
    if (request.headers.get('X-Inertia')) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(handleNavigation(request));

        return;
    }

    if (url.pathname.startsWith('/storage/')) {
        event.respondWith(
            cacheFirst(request, STATIC_CACHE).catch(() => Response.error()),
        );

        return;
    }

    if (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/images/') ||
        STATIC_PATTERN.test(url.pathname)
    ) {
        event.respondWith(
            staleWhileRevalidate(request, STATIC_CACHE, MAX_STATIC_ENTRIES),
        );
    }
});
