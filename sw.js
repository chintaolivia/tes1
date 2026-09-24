/**
 * Service Worker — Self-Destruct & Cache Purge
 * Little Salt Bread Blok M
 */

self.addEventListener("install", (e) => {
  self.skipWaiting();
});

self.addEventListener("activate", (e) => {
  e.waitUntil(
    caches
      .keys()
      .then((keys) => Promise.all(keys.map((k) => caches.delete(k))))
      .then(() => self.registration.unregister())
      .then(() => self.clients.claim()),
  );
});

// Pass all requests directly through to network with no caching
self.addEventListener("fetch", (e) => {
  e.respondWith(fetch(e.request));
});
