<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TestScope;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use OwenIt\Auditing\Contracts\Auditable;
use App\Enums\DartGameUserStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DartGameUser extends Pivot implements Auditable
{
    use HasFactory,
        SoftDeletes,
        \OwenIt\Auditing\Auditable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dart_game_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dart_game_id',
        'user_id',
        'dart_team_id',
        'status',
        'position',
        'place',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => DartGameUserStatus::class,
    ];

    /**
     * The team this player belongs to in this game.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(DartTeam::class, 'dart_team_id');
    }

    /**
     * Return user ids that are participating in active games.
     *
     * @param array $gameStatuses
     * @return array<int>
     */
    public static function activeUserIds(array $gameStatuses = ['created', 'started', 'running']): array
    {
        return static::join('dart_games', 'dart_game_user.dart_game_id', '=', 'dart_games.id')
            ->whereIn('dart_games.status', $gameStatuses)
            ->where('dart_game_user.status', 'accepted')
            ->whereNull('dart_game_user.deleted_at')
            ->pluck('dart_game_user.user_id')
            ->unique()
            ->values()
            ->toArray();
    }
}
