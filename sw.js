// SBO - Service Worker v2.0
// PWA Installation & Offline Support

const CACHE_NAME = 'sbo-cache-v2';
const STATIC_CACHE = 'sbo-static-v2';

// Arquivos para cache estático (app shell)
const urlsToCache = [
  './',
  './index.html',
  './offline.html',
  'https://cdn.tailwindcss.com',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
  'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap',
  'https://unpkg.com/dexie/dist/dexie.js',
  'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js',
  'https://cdn.jsdelivr.net/npm/sweetalert2@11',
  'https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js',
  'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
  'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js',
  'https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js',
  'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js',
  'https://www.gstatic.com/firebasejs/10.7.0/firebase-app-compat.js',
  'https://www.gstatic.com/firebasejs/10.7.0/firebase-firestore-compat.js',
  'https://www.gstatic.com/firebasejs/10.7.0/firebase-auth-compat.js'
];

// Install Event
self.addEventListener('install', event => {
  console.log('[Service Worker] Installing...');
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => {
        console.log('[Service Worker] Caching app shell');
        return cache.addAll(urlsToCache);
      })
      .catch(err => console.log('[Service Worker] Cache error:', err))
  );
  self.skipWaiting();
});

// Activate Event - Clean old caches
self.addEventListener('activate', event => {
  console.log('[Service Worker] Activating...');
  event.waitUntil(
    caches.keys().then(keyList => {
      return Promise.all(keyList.map(key => {
        if (key !== STATIC_CACHE && key !== CACHE_NAME) {
          console.log('[Service Worker] Removing old cache:', key);
          return caches.delete(key);
        }
      }));
    })
  );
  self.clients.claim();
});

// Fetch Event - Network First Strategy
self.addEventListener('fetch', event => {
  const requestUrl = event.request.url;
  
  // Skip non-GET requests and external analytics
  if (event.request.method !== 'GET') return;
  if (requestUrl.includes('chrome-extension')) return;
  if (requestUrl.includes('firestore.googleapis.com')) return;
  if (requestUrl.includes('googleapis.com')) return;
  if (requestUrl.includes('firebase')) return;
  
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Cache successful responses
        if (response && response.status === 200 && requestUrl.startsWith('http')) {
          const responseToCache = response.clone();
          caches.open(CACHE_NAME)
            .then(cache => {
              cache.put(event.request, responseToCache);
            })
            .catch(err => console.log('[Service Worker] Cache put error:', err));
        }
        return response;
      })
      .catch(() => {
        // If network fails, try cache
        return caches.match(event.request)
          .then(cachedResponse => {
            if (cachedResponse) {
              console.log('[Service Worker] Serving from cache:', requestUrl);
              return cachedResponse;
            }
            // Return offline page for HTML requests
            const acceptHeader = event.request.headers.get('accept') || '';
            if (acceptHeader.includes('text/html')) {
              return caches.match('./offline.html')
                .then(offlinePage => {
                  if (offlinePage) return offlinePage;
                  return new Response('Offline - SBO Sistem', {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: new Headers({ 'Content-Type': 'text/html' })
                  });
                });
            }
            return new Response('Offline - Conecte-se à internet', {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({ 'Content-Type': 'text/plain' })
            });
          });
      })
  );
});

// Push Notification Support (optional)
self.addEventListener('push', event => {
  const data = event.data ? event.data.json() : {};
  const title = data.title || 'SBO Sistem';
  const options = {
    body: data.body || 'Nova notificação do sistema',
    icon: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Crect width="100" height="100" fill="%231e3a5f"/%3E%3Ctext x="50" y="67" font-size="45" text-anchor="middle" fill="%23c9a03d" font-weight="bold"%3ESBO%3C/text%3E%3C/svg%3E',
    badge: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Crect width="100" height="100" fill="%231e3a5f"/%3E%3Ctext x="50" y="67" font-size="45" text-anchor="middle" fill="%23c9a03d" font-weight="bold"%3ESBO%3C/text%3E%3C/svg%3E',
    vibrate: [200, 100, 200],
    requireInteraction: true
  };
  event.waitUntil(self.registration.showNotification(title, options));
});

// Background Sync for offline data
self.addEventListener('sync', event => {
  if (event.tag === 'sync-data') {
    console.log('[Service Worker] Background sync triggered');
    event.waitUntil(syncOfflineData());
  }
});

async function syncOfflineData() {
  console.log('[Service Worker] Syncing offline data...');
  // This function can be extended to sync pending operations from IndexedDB
  return Promise.resolve();
}
