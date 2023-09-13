<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\InvitationStatus;


class Invitation extends Model
{
    use HasFactory,
        SoftDeletes;

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
