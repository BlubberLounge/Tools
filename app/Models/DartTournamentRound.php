<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Enums\DartTournamentRoundStatus;

class DartTournamentRound extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    protected $fillable = [
        'dart_tournament_id',
        'round_number',
        'name',
        'status',
    ];

    protected $casts = [
        'status' => DartTournamentRoundStatus::class,
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(DartTournament::class, 'dart_tournament_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(DartTournamentMatch::class)->orderBy('match_number', 'ASC');
    }
}
