const CACHE_NAME = 'mms-pwa-v2';
const urlsToCache = [
    '/',
    '/login',
    '/manifest.json',
    '/sw.js',
    '/offline.html',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/images/gold-security-logo.png'
];

self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function(cache) {
            return cache.addAll(urlsToCache);
        }).catch(function() {})
    );
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(names) {
            return Promise.all(
                names.filter(function(name) { return name !== CACHE_NAME; }).map(function(name) { return caches.delete(name); })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', function(event) {
    var request = event.request;
    var url = new URL(request.url);
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(function() {
                return caches.match('/offline.html').then(function(r) { return r || caches.match('/'); });
            })
        );
        return;
    }
    if (url.pathname.indexOf('/api/') === 0) {
        event.respondWith(fetch(request));
        return;
    }
    event.respondWith(
        caches.match(request).then(function(response) {
            return response || fetch(request);
        })
    );
});
