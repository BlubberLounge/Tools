<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DartPlayerInvitation extends Model
{
    protected $fillable = [
        'auth_invitation_token',
        'invited_by_user_id',
        'email',
        'firstname',
        'lastname',
        'local_player_id',
        'status',
        'registered_user_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function registeredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_user_id');
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'sent']);
    }

    public function markAsRegistered(User $user): void
    {
        $this->update([
            'status' => 'registered',
            'registered_user_id' => $user->id,
        ]);
    }
}
