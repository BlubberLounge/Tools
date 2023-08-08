<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

use jeremykenedy\LaravelRoles\Traits\HasRoleAndPermission;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements MustVerifyEmail, Auditable
{
    use HasFactory,
        Notifiable,
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
     *
     */
    public function getFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

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
    public static function getRootUser(): User
    {
        return User::where('name', 'root')->first();
    }

    /**
     * The DartGames that belong to the user.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function DartGames(): BelongsToMany
    {
        return $this->belongsToMany(DartGame::class)
            ->withTimestamps();
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
