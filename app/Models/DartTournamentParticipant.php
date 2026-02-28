<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DartTournamentParticipant extends Pivot
{
    protected $table = 'dart_tournament_participants';

    public $incrementing = true;

    protected $fillable = [
        'dart_tournament_id',
        'user_id',
        'seed_position',
        'final_placement',
        'eliminated',
        'wins',
        'losses',
        'points_for',
        'points_against',
    ];

    protected $casts = [
        'eliminated' => 'boolean',
    ];
}
