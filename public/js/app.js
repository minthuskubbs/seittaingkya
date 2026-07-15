/* Dental Clinic MS — PWA client: offline detection, sync queue, notifications, push */
(function () {
  const APP = window.APP || {};

  /* ---------- Online / offline banner ---------- */
  function setOnline(on) {
    document.body.classList.toggle('is-offline', !on);
    if (on) OfflineQueue.flush();
  }
  window.addEventListener('online', () => setOnline(true));
  window.addEventListener('offline', () => setOnline(false));
  setOnline(navigator.onLine);

  /* ---------- Service worker ---------- */
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js').then(reg => {
      subscribePush(reg);
    }).catch(() => {});
  }

  /* ---------- Web push subscription ---------- */
  async function subscribePush(reg) {
    try {
      if (!('PushManager' in window) || !APP.vapid) return;
      if (Notification.permission === 'denied') return;
      if (Notification.permission === 'default') {
        const p = await Notification.requestPermission();
        if (p !== 'granted') return;
      }
      let sub = await reg.pushManager.getSubscription();
      if (!sub) {
        sub = await reg.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: urlBase64ToUint8Array(APP.vapid),
        });
      }
      await fetch(APP.urls.pushStore, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': APP.csrf },
        body: JSON.stringify(sub.toJSON()),
      });
    } catch (e) { /* push optional */ }
  }

  function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = atob(base64);
    return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
  }

  /* ---------- Notification bell ---------- */
  const shownIds = new Set();
  async function pollNotifications() {
    try {
      const res = await fetch(APP.urls.unread, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) return;
      const data = await res.json();
      const dot = document.getElementById('notifDot');
      const list = document.getElementById('notifList');
      if (dot) dot.classList.toggle('d-none', data.count === 0);
      if (list) {
        list.innerHTML = data.items.length
          ? data.items.map(n => `<a class="dropdown-item py-2 border-bottom" href="#">
               <div class="fw-semibold small">${escapeHtml(n.title)}</div>
               <div class="small text-muted">${escapeHtml(n.message)}</div>
               <div class="small text-muted">${escapeHtml(n.created_at)}</div></a>`).join('')
          : '<div class="text-muted small p-3 text-center">No new notifications.</div>';
      }
      // Desktop / mobile browser notification for new items.
      if (Notification.permission === 'granted') {
        data.items.forEach(n => {
          if (!shownIds.has(n.id)) {
            shownIds.add(n.id);
            try { new Notification(n.title, { body: n.message, icon: '/vendor/pwa/icon-192.png' }); } catch (e) {}
          }
        });
      }
    } catch (e) {}
  }
  window.markAllRead = async function (e) {
    e.preventDefault();
    await fetch(APP.urls.read, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': APP.csrf }, body: '{}' });
    pollNotifications();
  };
  function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

  if (document.getElementById('notifBtn')) {
    pollNotifications();
    setInterval(pollNotifications, 30000);
  }

  /* ---------- Offline write queue (IndexedDB) ---------- */
  const OfflineQueue = {
    db: null,
    open() {
      return new Promise((resolve) => {
        if (this.db) return resolve(this.db);
        const req = indexedDB.open('dental-offline', 1);
        req.onupgradeneeded = e => e.target.result.createObjectStore('ops', { keyPath: 'uuid' });
        req.onsuccess = e => { this.db = e.target.result; resolve(this.db); };
        req.onerror = () => resolve(null);
      });
    },
    async add(op) {
      const db = await this.open(); if (!db) return;
      db.transaction('ops', 'readwrite').objectStore('ops').put(op);
    },
    async all() {
      const db = await this.open(); if (!db) return [];
      return new Promise(res => {
        const r = db.transaction('ops').objectStore('ops').getAll();
        r.onsuccess = () => res(r.result || []);
        r.onerror = () => res([]);
      });
    },
    async remove(uuid) {
      const db = await this.open(); if (!db) return;
      db.transaction('ops', 'readwrite').objectStore('ops').delete(uuid);
    },
    async flush() {
      if (!navigator.onLine) return;
      const ops = await this.all();
      if (!ops.length) return;
      try {
        const res = await fetch(APP.urls.sync, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': APP.csrf },
          body: JSON.stringify({ operations: ops }),
        });
        if (res.ok) {
          const data = await res.json();
          for (const uuid of Object.keys(data.results || {})) await this.remove(uuid);
          if (ops.length) showToast(`${ops.length} offline change(s) synced.`);
        }
      } catch (e) {}
    },
  };
  window.OfflineQueue = OfflineQueue;

  /* Intercept forms marked data-offline="patient" so they queue when offline. */
  document.addEventListener('submit', async function (e) {
    const form = e.target;
    const entity = form.getAttribute('data-offline');
    if (!entity || navigator.onLine) return; // online -> normal submit
    e.preventDefault();
    const fd = new FormData(form);
    const payload = {};
    fd.forEach((v, k) => { if (k !== '_token' && k !== '_method') payload[k] = v; });
    const uuid = (crypto.randomUUID && crypto.randomUUID()) || (Date.now() + '-' + Math.random());
    const action = form.getAttribute('data-offline-action') || 'create';
    await OfflineQueue.add({ entity, action, uuid, payload });
    showToast('Saved offline. Will sync when back online.');
    if (form.dataset.offlineRedirect) location.href = form.dataset.offlineRedirect;
    else form.reset();
  });

  function showToast(msg) {
    let host = document.getElementById('toastHost');
    if (!host) { host = document.createElement('div'); host.id = 'toastHost';
      host.style.cssText = 'position:fixed;bottom:16px;right:16px;z-index:2000;'; document.body.appendChild(host); }
    const el = document.createElement('div');
    el.className = 'toast show align-items-center text-bg-dark border-0 mb-2';
    el.innerHTML = `<div class="d-flex"><div class="toast-body">${escapeHtml(msg)}</div>
      <button class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button></div>`;
    host.appendChild(el);
    setTimeout(() => el.remove(), 5000);
  }
  window.showToast = showToast;

  OfflineQueue.flush();
})();
