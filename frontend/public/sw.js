/**
 * Service Worker — Ultreiataku (ULTREIA-19 + ULTREIA-57)
 *
 * Stratégies :
 *   - App shell (HTML/CSS/JS)         : Cache First
 *   - OSM tiles                       : Cache First 30j, zooms 12-16
 *   - API JSON /api/pilgrimage/*      : Stale-While-Revalidate 24h
 *   - GeoJSON /api/pilgrimage/gpx/*   : Cache First 7j
 *   - Photos journal /journal/photos/ : Cache First 24h (ULTREIA-57)
 *   - Autres                          : Network First
 */

const SHELL_CACHE   = 'ultreia-shell-v1';
const TILES_CACHE   = 'ultreia-tiles-v1';
const API_CACHE     = 'ultreia-api-v1';
const GPX_CACHE     = 'ultreia-gpx-v1';
const PHOTOS_CACHE  = 'ultreia-journal-photos-v1';

const TILES_MAX_AGE_MS  = 30 * 24 * 60 * 60 * 1000; // 30 jours
const API_MAX_AGE_MS    = 24 * 60 * 60 * 1000;        // 24h
const GPX_MAX_AGE_MS    = 7  * 24 * 60 * 60 * 1000;  // 7 jours
const PHOTOS_MAX_AGE_MS = 24 * 60 * 60 * 1000;        // 24h (ULTREIA-57)

const SHELL_URLS = [
  '/',
  '/belgique',
  '/index.html',
];

// ── Install ──────────────────────────────────────────────────
self.addEventListener('install', (/** @type {ExtendableEvent} */ event) => {
  event.waitUntil(
    caches.open(SHELL_CACHE).then(cache => cache.addAll(SHELL_URLS)).then(() => self.skipWaiting())
  );
});

// ── Activate ─────────────────────────────────────────────────
self.addEventListener('activate', (/** @type {ExtendableEvent} */ event) => {
  const validCaches = [SHELL_CACHE, TILES_CACHE, API_CACHE, GPX_CACHE, PHOTOS_CACHE];
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => !validCaches.includes(k)).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

// ── Fetch ────────────────────────────────────────────────────
self.addEventListener('fetch', (/** @type {FetchEvent} */ event) => {
  const url = new URL(event.request.url);

  // OSM tiles — Cache First 30j, zooms 12-16 seulement
  if (url.hostname.endsWith('tile.openstreetmap.org')) {
    const parts = url.pathname.split('/');
    const zoom = parseInt(parts[1] ?? '0', 10);
    if (zoom >= 12 && zoom <= 16) {
      event.respondWith(cacheFirst(event.request, TILES_CACHE, TILES_MAX_AGE_MS));
    }
    return;
  }

  // GeoJSON GPX simplifié — Cache First 7j (avant le catch-all API ci-dessous)
  if (url.pathname.includes('/api/pilgrimage/gpx/') && url.pathname.includes('/simplified')) {
    event.respondWith(cacheFirst(event.request, GPX_CACHE, GPX_MAX_AGE_MS));
    return;
  }

  // ULTREIA-57 : Photos journal — Cache First 24h
  // Seulement les GET (pas les POST/DELETE) — les uploads passent en Network First
  if (
    url.pathname.includes('/api/pilgrimage/journal/photos/') &&
    event.request.method === 'GET'
  ) {
    event.respondWith(cacheFirst(event.request, PHOTOS_CACHE, PHOTOS_MAX_AGE_MS));
    return;
  }

  // API Pilgrimage — Stale-While-Revalidate 24h
  if (url.pathname.startsWith('/api/pilgrimage/')) {
    event.respondWith(staleWhileRevalidate(event.request, API_CACHE, API_MAX_AGE_MS));
    return;
  }

  // App shell (navigation HTML)
  if (event.request.mode === 'navigate') {
    event.respondWith(
      caches.match('/index.html').then(cached => cached ?? fetch(event.request))
    );
    return;
  }

  // Assets JS/CSS — Cache First (Vite hash = immutable)
  if (url.pathname.match(/\.(js|css|woff2?|svg|png|ico)$/)) {
    event.respondWith(cacheFirst(event.request, SHELL_CACHE, Infinity));
    return;
  }
});

// ── Stratégies ────────────────────────────────────────────────

/**
 * Cache First : sert depuis le cache si frais, sinon réseau + mise en cache.
 * @param {Request} request
 * @param {string} cacheName
 * @param {number} maxAgeMs
 * @returns {Promise<Response>}
 */
async function cacheFirst(request, cacheName, maxAgeMs) {
  const cache = await caches.open(cacheName);
  const cached = await cache.match(request);
  if (cached) {
    const date = cached.headers.get('date');
    const age = date ? Date.now() - new Date(date).getTime() : 0;
    if (age < maxAgeMs) return cached;
  }
  try {
    const fresh = await fetch(request);
    if (fresh.ok) await cache.put(request, fresh.clone());
    return fresh;
  } catch {
    if (cached) return cached;
    return new Response('Offline', { status: 503 });
  }
}

/**
 * Stale-While-Revalidate : sert le cache immédiatement, revalide en arrière-plan.
 * @param {Request} request
 * @param {string} cacheName
 * @param {number} maxAgeMs
 * @returns {Promise<Response>}
 */
async function staleWhileRevalidate(request, cacheName, maxAgeMs) {
  const cache = await caches.open(cacheName);
  const cached = await cache.match(request);

  const fetchPromise = fetch(request).then(fresh => {
    if (fresh.ok) cache.put(request, fresh.clone());
    return fresh;
  }).catch(() => null);

  if (cached) {
    const date = cached.headers.get('date');
    const age = date ? Date.now() - new Date(date).getTime() : 0;
    if (age < maxAgeMs) return cached;
  }

  const fresh = await fetchPromise;
  if (fresh) return fresh;
  if (cached) return cached;
  return new Response(JSON.stringify({ error: 'offline' }), {
    status: 503,
    headers: { 'Content-Type': 'application/json' },
  });
}

// ── Background Sync fallback ──────────────────────────────────
self.addEventListener('sync', (/** @type {SyncEvent} */ event) => {
  if (event.tag === 'journal-sync') {
    event.waitUntil(syncPendingJournal());
  }
});

async function syncPendingJournal() {
  // La logique de sync est déléguée au composant React via postMessage
  const clients = await self.clients.matchAll({ type: 'window' });
  clients.forEach(client => client.postMessage({ type: 'journal-sync-trigger' }));
}
