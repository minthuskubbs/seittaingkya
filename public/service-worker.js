/* Dental Clinic MS service worker — app-shell caching + push notifications */
const CACHE = 'dental-shell-v1';
const SHELL = [
  '/css/app.css',
  '/js/app.js',
  '/vendor/bootstrap/bootstrap.min.css',
  '/vendor/bootstrap/bootstrap.bundle.min.js',
  '/vendor/icons/bootstrap-icons.css',
  '/vendor/alpine/alpine.min.js',
  '/vendor/pwa/icon-192.png',
  '/offline.html',
];

self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE).then(c => c.addAll(SHELL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const req = event.request;
  if (req.method !== 'GET') return; // never cache writes

  const url = new URL(req.url);
  // Cache-first for static assets.
  if (/\.(css|js|png|jpg|jpeg|woff2?|svg)$/.test(url.pathname)) {
    event.respondWith(caches.match(req).then(hit => hit || fetch(req).then(res => {
      const copy = res.clone();
      caches.open(CACHE).then(c => c.put(req, copy));
      return res;
    }).catch(() => hit)));
    return;
  }
  // Network-first for pages, fall back to offline shell.
  event.respondWith(
    fetch(req).catch(() => caches.match(req).then(hit => hit || caches.match('/offline.html')))
  );
});

/* Push from server (low stock etc.) */
self.addEventListener('push', event => {
  let data = {};
  try { data = event.data.json(); } catch (e) { data = { title: 'Notification', body: event.data && event.data.text() }; }
  const title = data.title || 'Dental Clinic MS';
  event.waitUntil(self.registration.showNotification(title, {
    body: data.body || '',
    icon: data.icon || '/vendor/pwa/icon-192.png',
    badge: '/vendor/pwa/icon-192.png',
    data: data.data || {},
  }));
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  const url = (event.notification.data && event.notification.data.url) || '/dashboard';
  event.waitUntil(clients.matchAll({ type: 'window' }).then(list => {
    for (const c of list) { if ('focus' in c) return c.focus(); }
    return clients.openWindow(url);
  }));
});
