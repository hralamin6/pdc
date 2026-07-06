"use strict";

const CACHE_NAME = "pwa-cache-v1";
const OFFLINE_URL = '/offline.html';

// Install
self.addEventListener("install", (event) => {
    console.log('[SW] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll([OFFLINE_URL, '/logo.png']);
        }).then(() => self.skipWaiting())
    );
});

// Activate
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating...');
    event.waitUntil(self.clients.claim());
});

// Fetch
self.addEventListener("fetch", (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        );
    }
});

// Push notification
self.addEventListener('push', (event) => {
    console.log('[SW] Push received');

    let data = {
        title: 'Notification',
        body: 'You have a new notification',
        icon: '/logo.png',
        badge: '/logo.png',
        data: { url: '/' },
        actions: []
    };

    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            console.error('[SW] Failed to parse push data:', e);
        }
    }

    const options = {
        body: data.body,
        icon: data.icon || '/logo.png',
        badge: data.badge || '/logo.png',
        vibrate: [200, 100, 200],
        data: data.data || {},
        tag: data.tag || 'notification',
        requireInteraction: data.requireInteraction || false,
        renotify: data.renotify || false,
        actions: data.actions || [],
        timestamp: data.timestamp || Date.now(),
        silent: false
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification click
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification clicked', event.action);
    event.notification.close();

    const notificationData = event.notification.data || {};
    const action = event.action;
    let urlToOpen = notificationData.url || '/';

    // Handle different actions
    if (action === 'reply') {
        // Open chat page for quick reply
        urlToOpen = notificationData.url;
        console.log('[SW] Reply action - opening chat');
    } else if (action === 'view') {
        // Open chat page to view message
        urlToOpen = notificationData.url;
        console.log('[SW] View action - opening chat');
    } else if (action === 'mark_read') {
        // Mark as read via API call
        console.log('[SW] Mark as read action');
        event.waitUntil(
            fetch('/api/messages/' + notificationData.message_id + '/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'include'
            }).then(response => {
                console.log('[SW] Message marked as read');
            }).catch(error => {
                console.error('[SW] Failed to mark as read:', error);
            })
        );
        return; // Don't open window for mark as read
    }

    // Open or focus window
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Check if chat page is already open
                for (let client of clientList) {
                    const clientUrl = new URL(client.url);
                    const targetUrl = new URL(urlToOpen, self.location.origin);
                    
                    if (clientUrl.pathname === targetUrl.pathname && 'focus' in client) {
                        console.log('[SW] Focusing existing window');
                        return client.focus();
                    }
                }
                
                // Open new window if not found
                if (clients.openWindow) {
                    console.log('[SW] Opening new window:', urlToOpen);
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});

