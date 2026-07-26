/* Ultreïataku — service worker : appli hors-ligne + cache des tuiles carto */
const CORE = 'ultreia-core-v3';
const TILES = 'ultreia-tiles-v1';
const APP = ['./', './index.html', './manifest.webmanifest', './icon.svg'];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(CORE).then(c => c.addAll(APP)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(ks => Promise.all(
      ks.filter(k => k !== CORE && k !== TILES).map(k => caches.delete(k))
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);

  // Document / navigation : RÉSEAU D'ABORD (pour recevoir les mises à jour), cache en secours hors-ligne
  if (req.mode === 'navigate' || (url.origin === location.origin && /\/(index\.html)?$/.test(url.pathname))) {
    e.respondWith(
      fetch(req).then(r => {
        if (r && r.ok) { const cl = r.clone(); caches.open(CORE).then(c => c.put(req, cl)); return r; }
        // site éteint (dépôt privé → 404) ou erreur : on sert la copie en cache
        return caches.match(req).then(hit => hit || caches.match('./index.html')).then(hit => hit || r);
      }).catch(() => caches.match(req).then(hit => hit || caches.match('./index.html')))
    );
    return;
  }

  // Tuiles cartographiques : cache à la volée, réseau sinon (stale-while-revalidate)
  if (/tile\.openstreetmap\.org|opentopomap\.org|tiles|basemaps/i.test(url.host + url.pathname)) {
    e.respondWith(caches.open(TILES).then(async c => {
      const hit = await c.match(req);
      const net = fetch(req).then(r => { if (r && r.status === 200) c.put(req, r.clone()); return r; }).catch(() => hit);
      return hit || net;
    }));
    return;
  }

  // Autres ressources même origine : cache d'abord, réseau en secours
  e.respondWith(
    caches.match(req).then(hit => hit || fetch(req).then(r => {
      if (r && r.status === 200 && url.origin === location.origin) {
        const cl = r.clone();
        caches.open(CORE).then(c => c.put(req, cl));
      }
      return r;
    }).catch(() => caches.match('./index.html')))
  );
});
