<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

use App\Enums\DeviceType;


class Device extends Model implements Auditable
{
    use HasFactory,
        \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'device_type',
        'browser',
        'browser_version',
        'platform',
        'platform_version',
        'ip',
        'data',
        'login_count',
        'verified_at',
        'last_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'device_type' => DeviceType::class,
        'data' => 'array',
        'verified_at' => 'datetime',
        'last_active' => 'datetime',
    ];

    /**
     * Attributes to exclude in the Audit.
     *
     * @var array
     */
    protected $auditExclude = [
        'last_active',
    ];

    /**
     * Get all of the Users that own this device
     */
    public function users(): belongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     *
     */
    public function getIsVerifiedAttribute(): bool
    {
        return !is_null($this->verified_at);
    }
}
