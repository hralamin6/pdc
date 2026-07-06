<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use NotificationChannels\WebPush\PushSubscription;
use App\Models\GuestSubscription;
use function Laravel\Prompts\alert;

class PushSubscriptionController extends Controller
{
    /**
     * Subscribe user to push notifications (handles both guest and authenticated users)
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
            'keys.auth' => 'required|string',
            'keys.p256dh' => 'required|string',
            'device_id' => 'nullable|string', // Browser fingerprint for guest tracking
        ]);

        $user = Auth::user();

        if ($user) {
            // Authenticated user subscription
            return $this->subscribeAuthenticatedUser($user, $validated);
        } else {
            // Guest user subscription
            return $this->subscribeGuestUser($validated, $request);
        }
    }

    /**
     * Handle authenticated user subscription
     */
    private function subscribeAuthenticatedUser($user, $validated)
    {
        // Check if there's a guest subscription to migrate
        $guestSubscription = GuestSubscription::where('endpoint', $validated['endpoint'])->first();

        if (!$guestSubscription && isset($validated['device_id'])) {
            // Try to find by device_id if endpoint doesn't match
            $guestSubscription = GuestSubscription::where('device_id', $validated['device_id'])->first();
        }

        // Delete any existing authenticated subscriptions with same endpoint
        PushSubscription::where('endpoint', $validated['endpoint'])->delete();

        // Create new subscription for authenticated user
        $user->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth']
        );

        // If there was a guest subscription, delete it after migration
        if ($guestSubscription) {
            $guestSubscription->delete();
            $message = 'Successfully migrated guest subscription to authenticated user';
        } else {
            $message = 'Successfully subscribed to push notifications';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'type' => 'authenticated'
        ], 200);
    }

    /**
     * Handle guest user subscription
     */
    private function subscribeGuestUser($validated, $request)
    {
        $sessionId = $request->session()->getId();
        $deviceId = $validated['device_id'] ?? null;

        // Create or update guest subscription
        $guestSubscription = GuestSubscription::createOrUpdateForGuest(
            $validated,
            $sessionId,
            $deviceId
        );

        return response()->json([
            'success' => true,
            'message' => 'Successfully subscribed as guest to push notifications',
            'type' => 'guest',
            'guest_id' => $guestSubscription->id
        ], 200);
    }

    /**
     * Unsubscribe user from push notifications
     */
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
        ]);

        $user = Auth::user();

        if ($user) {
            // Authenticated user unsubscribe
            $user->pushSubscriptions()
                ->where('endpoint', $validated['endpoint'])
                ->delete();
        } else {
            // Guest user unsubscribe
            GuestSubscription::where('endpoint', $validated['endpoint'])->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully unsubscribed from push notifications'
        ], 200);
    }

    /**
     * Get subscription status
     */
    public function status(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $subscriptionCount = $user->pushSubscriptions()->count();
            return response()->json([
                'subscribed' => $subscriptionCount > 0,
                'count' => $subscriptionCount,
                'type' => 'authenticated'
            ]);
        } else {
            // Check guest subscriptions
            $sessionId = $request->session()->getId();
            $guestCount = GuestSubscription::where('session_id', $sessionId)->count();

            return response()->json([
                'subscribed' => $guestCount > 0,
                'count' => $guestCount,
                'type' => 'guest'
            ]);
        }
    }

    /**
     * Migrate guest subscriptions to authenticated user (called after login)
     */
    public function migrateGuestSubscriptions(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $sessionId = $request->session()->getId();
        $deviceId = $request->input('device_id');

        // Find guest subscriptions to migrate
        $query = GuestSubscription::where('session_id', $sessionId);

        if ($deviceId) {
            $query->orWhere('device_id', $deviceId);
        }

        $guestSubscriptions = $query->get();
        $migratedCount = 0;

        foreach ($guestSubscriptions as $guestSubscription) {
            try {
                $guestSubscription->migrateToUser($user);
                $migratedCount++;
            } catch (\Exception $e) {
                \Log::error('Failed to migrate guest subscription: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'migrated' => $migratedCount,
            'message' => "Successfully migrated {$migratedCount} guest subscriptions"
        ]);
    }

    /**
     * Get VAPID public key
     */
    public function vapidPublicKey()
    {
        return response()->json([
            'publicKey' => config('webpush.vapid.public_key')
        ]);
    }
}

