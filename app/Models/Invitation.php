<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable;
use App\Enums\InvitationStatus;


class Invitation extends Model implements Auditable
{
    use HasFactory,
        SoftDeletes,
        \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'token',
        'status',
        'firstname',
        'lastname',
        'email',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => InvitationStatus::class,
        'expires_at' => 'datetime',
    ];

    /**
     *
     */
    public function getFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     *
     */
    public function isExpired(): bool
    {
        if(!$this->expires_at)
            return false;

        $isExpired = $this->expires_at->isPast();

        if($isExpired)
            $this->status = InvitationStatus::EXPIRED;

        return $isExpired;
    }
}
