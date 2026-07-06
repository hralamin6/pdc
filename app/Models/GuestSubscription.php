<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
        'session_id',
        'device_id',
    ];

    /**
     * Find subscription by endpoint
     */
    public static function findByEndpoint($endpoint)
    {
        return static::where('endpoint', $endpoint)->first();
    }

    /**
     * Create or update subscription for guest
     */
    public static function createOrUpdateForGuest($subscriptionData, $sessionId = null, $deviceId = null)
    {
        return static::updateOrCreate(
            ['endpoint' => $subscriptionData['endpoint']],
            [
                'public_key' => $subscriptionData['keys']['p256dh'] ?? null,
                'auth_token' => $subscriptionData['keys']['auth'] ?? null,
                'content_encoding' => $subscriptionData['content_encoding'] ?? null,
                'session_id' => $sessionId,
                'device_id' => $deviceId,
            ]
        );
    }

    /**
     * Convert to webpush format
     */
    public function toWebPushFormat()
    {
        return [
            'endpoint' => $this->endpoint,
            'publicKey' => $this->public_key,
            'authToken' => $this->auth_token,
            'contentEncoding' => $this->content_encoding,
        ];
    }

    /**
     * Migrate guest subscription to authenticated user
     */
    public function migrateToUser($user)
    {
        // Create push subscription for the authenticated user
        $user->updatePushSubscription(
            $this->endpoint,
            $this->public_key,
            $this->auth_token,
            $this->content_encoding
        );

        // Delete the guest subscription
        $this->delete();

        return true;
    }
}
