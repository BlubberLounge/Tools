<?php

namespace App\Models;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Carbon\Carbon;

use jeremykenedy\LaravelRoles\Traits\HasRoleAndPermission;
use OwenIt\Auditing\Contracts\Auditable;
use DarkGhostHunter\Laraconfig\HasConfig;
use App\Classes\DeviceTracker;

class User extends Authenticatable implements MustVerifyEmail, Auditable, HasLocalePreference
{
    use HasFactory,
        Notifiable,
        HasConfig,
        HasApiTokens,
        HasRoleAndPermission,
        \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'firstname',
        'lastname',
        'email',
        'dob',
        'img',
        'password',
        'locked',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dob' => 'date',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Attributes to include in the Audit.
     *
     * @var array
     */
    protected $auditInclude = [
        //
    ];

    /**
     * Profile picture Accessor
     */
    protected function img(): Attribute
    {
        return Attribute::make(
            get: function (string|null $value) {
                if(is_null($value))
                    return null;
                    // return Avatar::create(Auth::user()->name)->setDimension(50)->setFontSize(28)->toBase64();
                return $value;
            }
        );
    }

    /**
     *
     */
    public function getLastSeenAttribute(): Carbon
    {
        $lastActiveDevice = $this->devices
            ->sortByDesc('last_active')
            ->first();

        if(!$lastActiveDevice)
            return Carbon::createFromTimestamp(0); // 1970-01-01

        return $lastActiveDevice->last_active;
    }

    /**
     *
     */
    public function getFullNameAttribute(): String
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     *
     */
    public function scopeLike($query, $field, $value)
    {
        return $query->where($field, 'LIKE', "%$value%");
    }

    /**
     *
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     *
     */
    public function isOnline()
    {
        return now()->diffInMinutes($this->last_seen) <= 1;
    }

    /**
     *
     */
    public function preferredLocale()
    {
        return $this->locale;
    }

    /**
     *
     */
    public static function getRootUser(): User
    {
        return User::where('name', 'root')->first();
    }

    /**
     * Get all of the Devices
     */
    public function devices(): hasMany
    {
        return $this->hasMany(Device::class)
            ->orderBy('verified_at', 'desc')
            ->orderBy('last_active', 'desc');
    }

    /**
     *
     */
    public function currentDevice()
    {
        return DeviceTracker::detect(false);
    }

    /**
     * Get all of the user Feedback
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class)
            ->orderBy('created_at', 'DESC');
    }

    /**
     * The DartGames that belong to the user.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function DartGames(bool $withTimestamps = true, bool $withPivot = true): BelongsToMany
    {
        $relation = $this->belongsToMany(DartGame::class);

        if($withTimestamps)
            $relation->withTimestamps();

        if($withPivot)
            $relation->withPivot(['status', 'position', 'place']);

        return $relation;
    }

    /**
     * Get all of the throws for the uder.
     */
    public function DartThrows(): HasMany
    {
        return $this->hasMany(DartThrow::class);
    }

    /**
     * The DartGames that this user created
     */
    public function createdGames(): HasMany
    {
        return $this->hasMany(DartGame::class, 'created_by');
    }

    /**
     * Get all of the throws for a user by game id
     */
    // public function throwsByGame(): HasMany
    // {
    //     return $this->throws()->where('dart_game_id', '99c7dc3b-7e5f-4db5-8fb2-313660a1b1a5');
    // }

    /**
     *
     */
    public function getGameTitle(): string
    {
        return $this->full_name.'\'s Game #'. Str::padLeft($this->createdGames->count(), 3, 0);
    }

    /**
     *
     */
    public function getThrowCount($gameId): int
    {
        return $this->DartThrows()->where('dart_game_id', $gameId)->count();;
    }

    /**
     *
     */
    public function getThrowAverage($gameId): float
    {
        $sum = $this->getThrowCount($gameId);
        $throws = $this->DartThrows()->where('dart_game_id', $gameId)->get();
        if(count($throws) <= 0)
            return -1;

        foreach($throws as $throw) $sum += $throw->value;
        return round($sum / count($throws), 1);
    }
}
