// sw.js - Service Worker for SBO System (Düzeltilmiş)
const CACHE_NAME = 'sbo-cache-v3';
const RUNTIME_CACHE = 'sbo-runtime-v1';

// Önbelleğe alınacak statik kaynaklar
const urlsToCache = [
    './',
    './index.html',
    './offline.html', // Offline sayfası eklenmeli
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://unpkg.com/dexie/dist/dexie.js',
    'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    // DÜZELTİLDİ: SheetJS URL
    'https://cdn.sheetjs.com/xlsx-0.20.2/xlsx.full.min.js',
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
    'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js',
    'https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js',
    'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js'
];

// KALDIRILDI: Tailwind CDN - dinamik içerik cache'lenmez

// Install Event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Önbelleğe alınıyor...');
                return cache.addAll(urlsToCache).catch(err => {
                    console.warn('Bazı kaynaklar önbelleğe alınamadı:', err);
                    // Hata olsa bile devam et
                    return Promise.resolve();
                });
            })
            .then(() => self.skipWaiting())
    );
});

// Fetch Event - Gelişmiş Strateji
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // API istekleri - Network First
    if (url.pathname.includes('/api/') || url.pathname.includes('/graphql')) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Navigasyon istekleri (HTML sayfalar)
    if (request.mode === 'navigate') {
        event.respondWith(navigationHandler(request));
        return;
    }

    // CDN kaynakları ve statik dosyalar - Cache First + Network Fallback
    if (urlsToCache.some(cacheUrl => request.url.includes(cacheUrl.split('/').pop()))) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Diğer istekler - Stale While Revalidate
    event.respondWith(staleWhileRevalidate(request));
});

// Cache First Stratejisi
async function cacheFirst(request) {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) return cachedResponse;
    
    try {
        const networkResponse = await fetch(request);
        if (networkResponse && networkResponse.status === 200) {
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        // Offline fallback
        return new Response('Kaynak kullanılamıyor', { 
            status: 503, 
            statusText: 'Service Unavailable',
            headers: { 'Content-Type': 'text/plain' }
        });
    }
}

// Network First Stratejisi (API için)
async function netWorkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        if (networkResponse && networkResponse.status === 200) {
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) return cachedResponse;
        
        // API offline hatası
        return new Response(JSON.stringify({ 
            error: 'Offline', 
            message: 'İnternet bağlantısı yok' 
        }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

// Stale While Revalidate Stratejisi
async function staleWhileRevalidate(request) {
    const cache = await caches.open(RUNTIME_CACHE);
    const cachedResponse = await cache.match(request);
    
    const networkResponsePromise = fetch(request)
        .then(response => {
            if (response && response.status === 200) {
                cache.put(request, response.clone());
            }
            return response;
        })
        .catch(() => null);

    // Önbellekte varsa hemen döndür, yoksa ağdan bekle
    return cachedResponse || networkResponsePromise;
}

// Navigasyon Handler
async function navigationHandler(request) {
    try {
        const networkResponse = await fetch(request);
        if (networkResponse && networkResponse.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) return cachedResponse;
        
        // Offline sayfasına yönlendir
        const offlinePage = await caches.match('./offline.html');
        if (offlinePage) return offlinePage;
        
        return new Response(
            '<h1>Offline - SBO Sistema</h1><p>Lütfen internet bağlantınızı kontrol edin.</p>',
            { 
                status: 200, 
                headers: { 'Content-Type': 'text/html' }
            }
        );
    }
}

// Activate Event - Eski cache'leri temizle
self.addEventListener('activate', event => {
    const currentCaches = [CACHE_NAME, RUNTIME_CACHE];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (!currentCaches.includes(cacheName)) {
                        console.log('Eski cache siliniyor:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Opsiyonel: Background Sync
self.addEventListener('sync', event => {
    if (event.tag === 'sync-sbo-data') {
        event.waitUntil(syncData());
    }
});

async function syncData() {
    // IndexedDB'den bekleyen verileri al ve sunucuya gönder
    console.log('Arka plan senkronizasyonu çalışıyor...');
    // Dexie ile veritabanından veri çekme işlemleri burada
}

// Opsiyonel: Push Notification
self.addEventListener('push', event => {
    const data = event.data?.json() || { title: 'SBO Bildirim', body: 'Yeni bildirim var' };
    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/icon-192x192.png',
            badge: '/badge-72x72.png'
        })
    );
});
