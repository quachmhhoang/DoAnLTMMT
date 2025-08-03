// Service Worker for Push Notifications
const CACHE_NAME = 'cellphone-store-v1';
const urlsToCache = [
    '/',
    '/assets/css/style.css',
    '/assets/js/app.js',
    '/assets/js/notifications.js'
];

// Install event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(urlsToCache);
            })
    );
});

// Fetch event
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Return cached version or fetch from network
                return response || fetch(event.request);
            })
    );
});

// Push event - Handle incoming push notifications
self.addEventListener('push', event => {
    console.log('Push event received:', event);
    
    let notificationData = {
        title: 'CellPhone Store',
        body: 'Bạn có thông báo mới!',
        icon: '/assets/images/icon-192x192.png',
        badge: '/assets/images/badge-72x72.png',
        tag: 'default',
        requireInteraction: false,
        actions: [
            {
                action: 'view',
                title: 'Xem',
                icon: '/assets/images/view-icon.png'
            },
            {
                action: 'close',
                title: 'Đóng',
                icon: '/assets/images/close-icon.png'
            }
        ],
        data: {
            url: '/',
            timestamp: Date.now()
        }
    };

    if (event.data) {
        try {
            const payload = event.data.json();
            notificationData = {
                ...notificationData,
                ...payload
            };
        } catch (e) {
            console.error('Error parsing push payload:', e);
            notificationData.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(notificationData.title, {
            body: notificationData.body,
            icon: notificationData.icon,
            badge: notificationData.badge,
            tag: notificationData.tag,
            requireInteraction: notificationData.requireInteraction,
            actions: notificationData.actions,
            data: notificationData.data,
            vibrate: [200, 100, 200],
            timestamp: notificationData.data.timestamp
        })
    );
});

// Notification click event
self.addEventListener('notificationclick', event => {
    console.log('Notification clicked:', event);
    
    event.notification.close();
    
    const action = event.action;
    const notificationData = event.notification.data;
    
    if (action === 'close') {
        return;
    }
    
    let url = '/';
    if (action === 'view' && notificationData.url) {
        url = notificationData.url;
    } else if (notificationData.url) {
        url = notificationData.url;
    }
    
    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then(clientList => {
            // Check if there's already a window/tab open with the target URL
            for (const client of clientList) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            
            // If no window/tab is open, open a new one
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

// Notification close event
self.addEventListener('notificationclose', event => {
    console.log('Notification closed:', event);
    
    // Track notification close analytics if needed
    const notificationData = event.notification.data;
    
    // Send analytics data to server
    if (notificationData && notificationData.trackClose) {
        fetch('/api/notifications/track-close', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                notificationId: notificationData.notificationId,
                timestamp: Date.now()
            })
        }).catch(err => {
            console.error('Failed to track notification close:', err);
        });
    }
});

// Background sync for offline notifications
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync-notifications') {
        event.waitUntil(
            syncNotifications()
        );
    }
});

// Function to sync notifications when back online
async function syncNotifications() {
    try {
        const response = await fetch('/api/notifications/sync');
        const data = await response.json();
        
        if (data.notifications && data.notifications.length > 0) {
            for (const notification of data.notifications) {
                await self.registration.showNotification(notification.title, {
                    body: notification.message,
                    icon: '/assets/images/icon-192x192.png',
                    badge: '/assets/images/badge-72x72.png',
                    tag: `notification-${notification.id}`,
                    data: {
                        notificationId: notification.id,
                        url: notification.url || '/',
                        timestamp: Date.now()
                    }
                });
            }
        }
    } catch (error) {
        console.error('Failed to sync notifications:', error);
    }
}

// Handle messages from main thread
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Update event
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Periodic background sync (if supported)
self.addEventListener('periodicsync', event => {
    if (event.tag === 'check-notifications') {
        event.waitUntil(
            checkForNewNotifications()
        );
    }
});

async function checkForNewNotifications() {
    try {
        const response = await fetch('/api/notifications/check-new');
        const data = await response.json();
        
        if (data.hasNew) {
            await self.registration.showNotification('CellPhone Store', {
                body: 'Bạn có thông báo mới!',
                icon: '/assets/images/icon-192x192.png',
                tag: 'new-notifications',
                data: {
                    url: '/notifications',
                    timestamp: Date.now()
                }
            });
        }
    } catch (error) {
        console.error('Failed to check for new notifications:', error);
    }
}
