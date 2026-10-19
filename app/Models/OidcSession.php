<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A local session that was started by "Sign in with BlubberLounge".
 *
 * @property int $id
 * @property string $session_id
 * @property string|null $sid
 * @property int $user_id
 */
class OidcSession extends Model
{
    use MassPrunable;

    protected $fillable = [
        'session_id',
        'sid',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Old rows only matter for sid based back-channel logout. When a row is
     * gone, a logout token with a sub still ends all sessions of the user.
     */
    public function prunable(): Builder
    {
        return static::where('updated_at', '<', now()->subDays(30));
    }
}
