

// Service Worker untuk E-Presensi GPS V2
// TIDAK akan cache file apapun - semua data selalu fresh dari network
// FIX: Tidak mengintervensi navigation request (HTML) untuk mencegah blank page

// Install event - tidak cache apapun
self.addEventListener('install', event => {
    // Skip waiting untuk update service worker lebih cepat
    self.skipWaiting();
});

// Activate event - clear semua cache yang ada
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        return caches.delete(cacheName);
                    })
                );
            })
            .then(() => {
                return Promise.resolve();
            })
    );
});

// Background sync untuk presensi offline (opsional)
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync-presensi') {
        event.waitUntil(doBackgroundSync());
    }
});

async function doBackgroundSync() {
    // Implementasi sync data presensi jika diperlukan
}

// Message handler untuk komunikasi dengan main thread
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: '1.0.0-on-sw' });
    }
});

console.log('Service Worker: Unified Mode initialized');

// Message handler untuk komunikasi dengan main thread
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: '1.0.0-no-cache' });
    }
});

console.log('Service Worker: No Cache Mode initialized');

// Listener untuk menangkap sinyal Push dari Server
self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    let data = {};
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = { title: 'Presensi GPS', body: event.data.text() };
        }
    }

    const title = data.title || "Presensi GPS";
    const options = {
        body: data.body || "Ada pembaruan status presensi Anda.",
        icon: data.icon || "/assets/img/icon-192x192.png",
        badge: data.badge || "/assets/img/icon-96x96.png",
        vibrate: [100, 50, 100],
        data: {
            url: (data.data && data.data.action_url) || data.action_url || "/"
        }
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Listener saat notifikasi diklik oleh user
self.addEventListener('notificationclick', function (event) {
    event.notification.close(); // Tutup notifikasi

    // Buka link yang dikirim dari server
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            const targetUrl = event.notification.data.url;
            for (let i = 0; i < clientList.length; i++) {
                let client = clientList[i];
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
