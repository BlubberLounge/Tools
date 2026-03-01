<?php

namespace App\Models;

use App\Services\Dart\LocalPlayerResolver;
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

        // Backfill game data: associate any synced games/throws with the new user
        if ($this->local_player_id) {
            app(LocalPlayerResolver::class)->backfill($this->local_player_id, $user->id);
        }

        // Auto-create accepted acquaintance between inviter and registered user
        if ($this->invited_by_user_id && $this->invited_by_user_id !== $user->id) {
            $exists = Acquaintance::where(function ($q) use ($user) {
                $q->where('transmitter_user_id', $this->invited_by_user_id)
                  ->where('receiver_user_id', $user->id);
            })->orWhere(function ($q) use ($user) {
                $q->where('transmitter_user_id', $user->id)
                  ->where('receiver_user_id', $this->invited_by_user_id);
            })->exists();

            if (!$exists) {
                Acquaintance::create([
                    'transmitter_user_id' => $this->invited_by_user_id,
                    'receiver_user_id' => $user->id,
                    'status' => 'accepted',
                    'showOnHomeView' => true,
                ]);
            }
        }
    }
}
