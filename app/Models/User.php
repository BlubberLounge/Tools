<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     */
    public function DartGames(): BelongsToMany
    {
        return $this->belongsToMany(DartGame::class)->withTimestamps();
    }

    /**
     * Get all of the throws for the uder.
     */
    public function DartThrows(): HasMany
    {
        return $this->hasMany(DartThrow::class);
    }

    /**
     * Get all of the throws for a user by game id
     */
    public function throwsByGame(): HasMany
    {
        return $this->throws()->where('dart_game_id', '99c7dc3b-7e5f-4db5-8fb2-313660a1b1a5');
    }
}
