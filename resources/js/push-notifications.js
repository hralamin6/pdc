/**
 * Push Notification Manager - Clean Implementation
 */

class PushNotificationManager {
    constructor() {
        this.swUrl = '/sw.js';
        this.vapidKey = null;
        this.registration = null;
        this.autoSubscribeAttempted = false;
        this.deviceId = this.generateDeviceId(); // Add device ID
    }

    /**
     * Generate a simple device ID for guest tracking
     */
    generateDeviceId() {
        let deviceId = localStorage.getItem('push_device_id');
        if (!deviceId) {
            // Simple device fingerprint
            deviceId = 'device_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('push_device_id', deviceId);
            console.log('🆔 Generated device ID:', deviceId);
        }
        return deviceId;
    }

    async init() {
        if (!('serviceWorker' in navigator && 'PushManager' in window)) {
            console.warn('Push notifications not supported');
            return false;
        }

        try {
            await this.registerServiceWorker();
            await this.loadVapidKey();
            console.log('✅ Push notification manager initialized');
            return true;
        } catch (error) {
            console.error('❌ Init failed:', error);
            return false;
        }
    }

    async registerServiceWorker() {
        // Register the SW, but always use the `ready` registration.
        // Firefox throws DOMException from pushManager.getSubscription() if the
        // registration object still has state "installing" or "waiting".
        // navigator.serviceWorker.ready always resolves with the *active* registration.
        await navigator.serviceWorker.register(this.swUrl);
        this.registration = await navigator.serviceWorker.ready;
        console.log('✅ Service Worker ready');
    }

    async loadVapidKey() {
        const response = await fetch('/api/push/vapid-key');
        const data = await response.json();
        this.vapidKey = data.publicKey;
        console.log('✅ VAPID key loaded');
    }

    async subscribe() {
        // Request permission
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            throw new Error('Permission denied');
        }

        // Unsubscribe from old subscription first.
        // Use getSubscription() wrapper so Firefox DOMException is safely handled.
        const oldSub = await this.getSubscription();
        if (oldSub) {
            await oldSub.unsubscribe();
            console.log('🗑️ Removed old subscription');
        }

        // Create new subscription.
        // Firefox can throw DOMException: "Error retrieving push subscription" from
        // pushManager.subscribe() when its internal push.db is corrupt.
        // We try once normally, and if it fails with DOMException we throw a
        // typed error so autoSubscribe can handle it gracefully.
        let subscription;
        try {
            subscription = await this.registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.vapidKey)
            });
        } catch (err) {
            if (err instanceof DOMException) {
                // Tag the error so callers know this is a Firefox push.db issue
                err._firefoxPushDbCorrupt = true;
            }
            throw err;
        }

        console.log('📱 Browser subscribed:', subscription.endpoint.substring(0, 50) + '...');

        // Prepare subscription data with device ID
        const subscriptionData = subscription.toJSON();
        subscriptionData.device_id = this.deviceId; // Add device ID to request

        // Send to server
        const response = await fetch('/api/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(subscriptionData) // Send subscription data with device_id
        });

        if (!response.ok) {
            const error = await response.text();
            throw new Error('Server error: ' + error);
        }

        const result = await response.json();
        console.log('✅ Server saved subscription:', result);

        // Mark that we've successfully subscribed
        this.markAutoSubscribeAttempted(true);

        return subscription;
    }

    async unsubscribe() {
        // Use safe wrapper so Firefox DOMException is handled gracefully
        const subscription = await this.getSubscription();
        if (subscription) {
            await subscription.unsubscribe();

            await fetch('/api/push/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ endpoint: subscription.endpoint })
            });

            console.log('✅ Unsubscribed');
            return true;
        }
        return false;
    }

    /**
     * Nuclear reset: unregister the SW entirely to purge Firefox's corrupted push
     * subscription state, then re-register and retry subscribe() once.
     * Firefox stores push subscriptions in its own IndexedDB; unregistering the SW
     * is the only JS-accessible way to clear that state.
     */
    async nuclearResetAndSubscribe() {
        console.log('☢️ Nuclear SW reset: unregistering service worker...');

        // Unregister all service workers under this scope
        const registrations = await navigator.serviceWorker.getRegistrations();
        for (const reg of registrations) {
            await reg.unregister();
        }
        console.log('✅ SW unregistered. Re-registering...');

        // Re-register and wait for it to become active
        await navigator.serviceWorker.register(this.swUrl);
        this.registration = await navigator.serviceWorker.ready;
        console.log('✅ SW re-registered. Retrying subscribe...');

        // One clean retry — if this throws too, let it propagate
        return await this.registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: this.urlBase64ToUint8Array(this.vapidKey)
        });
    }

    async getSubscription() {
        if (!this.registration) {
            console.warn('Service Worker not registered yet');
            return null;
        }
        try {
            // Firefox can throw DOMException here if the push subscription
            // database is in an inconsistent state. Treat that as "no subscription".
            return await this.registration.pushManager.getSubscription();
        } catch (error) {
            console.warn('⚠️ getSubscription() threw (Firefox quirk):', error.message);
            return null;
        }
    }

    /**
     * Check if user has already been prompted for notification permission
     */
    hasAutoSubscribeAttempted() {
        return localStorage.getItem('push_auto_subscribe_attempted') === 'true';
    }

    /**
     * Mark that auto-subscribe has been attempted
     */
    markAutoSubscribeAttempted(success = false) {
        localStorage.setItem('push_auto_subscribe_attempted', 'true');
        if (success) {
            localStorage.setItem('push_subscribed_at', new Date().toISOString());
        }
    }

    /**
     * Check if already subscribed
     */
    async isSubscribed() {
        try {
            const browserSub = await this.getSubscription();
            if (!browserSub) {
                return false;
            }

            const response = await fetch('/api/push/status');
            if (!response.ok) return false;
            const status = await response.json();
            return browserSub && status.subscribed;
        } catch (error) {
            // Non-fatal: treat as "not subscribed" so we can re-attempt cleanly
            console.warn('⚠️ isSubscribed() check failed:', error.message);
            return false;
        }
    }

    /**
     * Auto-subscribe user on first visit
     */
    async autoSubscribe() {
        console.log('🚀 Starting auto-subscribe...');

        // Don't attempt if already tried or not initialized
        if (!this.registration || !this.vapidKey) {
            console.log('⏭️ Push manager not ready for auto-subscribe');
            return false;
        }

        // If Firefox's push.db is known to be broken on this profile, don't retry.
        // The user must clear Firefox site data manually to recover.
        if (localStorage.getItem('push_firefox_db_broken') === 'true') {
            console.warn('⚠️ Firefox push.db known broken. Clear site data in Firefox to recover.');
            return false;
        }

        // Check if already subscribed
        const alreadySubscribed = await this.isSubscribed();
        if (alreadySubscribed) {
            console.log('⏭️ Already subscribed to push notifications');
            this.markAutoSubscribeAttempted(true);
            return true;
        }

        // Check current permission state
        const permission = Notification.permission;
        console.log('📋 Current notification permission:', permission);

        if (permission === 'denied') {
            console.log('⏭️ Notification permission denied, skipping auto-subscribe');
            this.markAutoSubscribeAttempted(false);
            return false;
        }

        try {
            await this.subscribe();
            console.log('✅ Auto-subscribed successfully');
            return true;
        } catch (error) {
            if (error instanceof DOMException && error._firefoxPushDbCorrupt) {
                // Firefox push.db is corrupt — this cannot be fixed from JS.
                // Mark it so we stop retrying every page load.
                localStorage.setItem('push_firefox_db_broken', 'true');
                console.warn(
                    '⚠️ Firefox push notifications unavailable due to a corrupted internal push database.\n' +
                    'To fix: Firefox menu → Settings → Privacy & Security → Cookies and Site Data → ' +
                    'Manage Data → remove this site, then reload.\n' +
                    'Or run: window.fixFirefoxPush() in the console for instructions.'
                );
            } else {
                console.warn('⏭️ Auto-subscribe skipped:', error.message);
            }
            this.markAutoSubscribeAttempted(false);
            return false;
        }
    }

    /**
     * Reset auto-subscribe state (useful for testing)
     */
    resetAutoSubscribe() {
        localStorage.removeItem('push_auto_subscribe_attempted');
        localStorage.removeItem('push_subscribed_at');
        console.log('🔄 Auto-subscribe state reset');
    }

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
}

// Initialize
window.pushManager = new PushNotificationManager();

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', async () => {
    console.log('🎬 DOM loaded, initializing push manager...');
    const initialized = await window.pushManager.init();

    if (initialized) {
        console.log('✅ Push manager initialized, will attempt auto-subscribe in 2 seconds...');
        // Attempt auto-subscribe after a short delay (better UX)
        setTimeout(async () => {
            await window.pushManager.autoSubscribe();
        }, 2000); // 2 second delay to not interrupt page load
    } else {
        console.warn('⚠️ Push manager failed to initialize');
    }
});

// Also try on Livewire navigation
document.addEventListener('livewire:navigated', async () => {
    console.log('🔄 Livewire navigated');
    if (window.pushManager && !window.pushManager.registration) {
        const initialized = await window.pushManager.init();
        if (initialized && !window.pushManager.hasAutoSubscribeAttempted()) {
            setTimeout(async () => {
                await window.pushManager.autoSubscribe();
            }, 2000);
        }
    }
});

// Helper functions
window.subscribeToPush = async function() {
    try {
        await window.pushManager.subscribe();
        alert('✅ Subscribed to push notifications!');
        location.reload();
    } catch (error) {
        alert('❌ Failed: ' + error.message);
        console.error(error);
    }
};

window.unsubscribeFromPush = async function() {
    try {
        await window.pushManager.unsubscribe();
        alert('✅ Unsubscribed!');
        location.reload();
    } catch (error) {
        alert('❌ Failed: ' + error.message);
        console.error(error);
    }
};

window.checkPushStatus = async function() {
    const sub = await window.pushManager.getSubscription();
    console.log('Subscription:', sub);
    return sub;
};

// Debug helper to reset auto-subscribe
window.resetAutoSubscribe = function() {
    window.pushManager.resetAutoSubscribe();
    alert('Auto-subscribe state reset. Reload the page to try again.');
};

// Firefox push.db recovery instructions
window.fixFirefoxPush = function() {
    const steps = [
        '🔧 Firefox Push Notification Recovery',
        '',
        'Firefox has a corrupted internal push database for this site.',
        'Chrome/Edge are NOT affected — this is a Firefox-specific issue.',
        '',
        '📋 Steps to fix in Firefox:',
        '  1. Firefox menu (☰) → Settings',
        '  2. Privacy & Security → Cookies and Site Data → Manage Data',
        '  3. Search for this site → Remove Selected → Save Changes',
        '  4. Reload this page — push notifications will re-subscribe automatically.',
        '',
        '⚡ Quick alternative:',
        '  1. Open a new tab → type: about:serviceworkers',
        '  2. Find this site → click "Unregister"',
        '  3. Then clear site cookies: about:preferences#privacy → Manage Data',
        '',
        '🛠️ After fixing, run in the console:',
        '  window.resetFirefoxPushFlag()',
    ].join('\n');
    console.log(steps);
    alert(steps);
};

// Clear the Firefox broken-db flag so auto-subscribe retries after user fixes Firefox
window.resetFirefoxPushFlag = function() {
    localStorage.removeItem('push_firefox_db_broken');
    localStorage.removeItem('push_auto_subscribe_attempted');
    localStorage.removeItem('push_subscribed_at');
    console.log('✅ Firefox push flag cleared. Reload the page to retry auto-subscribe.');
    alert('✅ Flag cleared. Please reload the page to retry push subscription.');
};
