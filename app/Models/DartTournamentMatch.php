<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Enums\DartTournamentMatchStatus;

class DartTournamentMatch extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    protected $fillable = [
        'dart_tournament_id',
        'dart_tournament_round_id',
        'dart_game_id',
        'match_number',
        'player1_id',
        'player2_id',
        'winner_id',
        'status',
        'bracket_position',
    ];

    protected $casts = [
        'status' => DartTournamentMatchStatus::class,
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(DartTournament::class, 'dart_tournament_id');
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(DartTournamentRound::class, 'dart_tournament_round_id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(DartGame::class, 'dart_game_id');
    }

    public function player1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }
}
