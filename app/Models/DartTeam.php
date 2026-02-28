<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DartTeam extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';

    protected $fillable = [
        'dart_game_id',
        'name',
        'color',
        'position',
        'place',
    ];

    /**
     * The game this team belongs to.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(DartGame::class, 'dart_game_id');
    }

    /**
     * Users assigned to this team within the game.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'dart_game_user', 'dart_team_id', 'user_id')
            ->withPivot('status', 'position', 'place', 'dart_game_id')
            ->withTimestamps();
    }
}
