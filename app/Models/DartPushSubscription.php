<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DartPushSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'user_id',
        'endpoint',
        'p256dh_key',
        'auth_key',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Find subscription by endpoint.
     */
    public static function findByEndpoint(string $endpoint): ?self
    {
        return static::where('endpoint', $endpoint)->first();
    }

    /**
     * Find subscriptions by device ID.
     */
    public static function findByDeviceId(string $deviceId)
    {
        return static::where('device_id', $deviceId)->get();
    }
}
