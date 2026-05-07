const CACHE = 'bdi-apps-v2';
const OFFLINE_URL = '/offline';

const PRECACHE = ['/', '/offline', '/manifest.json', '/pengumuman', '/surat', '/keamanan'];

self.addEventListener('install', e => {
    e.waitUntil(caches.open(CACHE).then(c => c.addAll(PRECACHE)));
    self.skipWaiting();
});

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', e => {
    if (e.request.method !== 'GET') return;
    // Skip Livewire AJAX
    if (e.request.url.includes('/livewire/')) return;

    e.respondWith(
        fetch(e.request)
            .then(res => {
                if (res.ok) {
                    const clone = res.clone();
                    caches.open(CACHE).then(c => c.put(e.request, clone));
                }
                return res;
            })
            .catch(() => caches.match(e.request).then(r => r || caches.match(OFFLINE_URL)))
    );
});

// Push notification
self.addEventListener('push', e => {
    const data = e.data?.json() ?? { title: 'BDI Apps', body: 'Ada notifikasi baru' };
    e.waitUntil(
        self.registration.showNotification(data.notification?.title ?? data.title, {
            body: data.notification?.body ?? data.body,
            icon: '/icons/icon-192.png',
            data: { url: data.data?.url ?? '/' },
        })
    );
});

self.addEventListener('notificationclick', e => {
    e.notification.close();
    e.waitUntil(clients.openWindow(e.notification.data.url));
});
