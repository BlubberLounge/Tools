<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

use App\Enums\DartTournamentFormat;
use App\Enums\DartTournamentStatus;
use App\Enums\DartGameType;

class DartTournament extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';

    protected $fillable = [
        'created_by',
        'title',
        'format',
        'game_mode',
        'game_options',
        'seed_type',
        'best_of',
        'status',
        'winner_id',
    ];

    protected $casts = [
        'format' => DartTournamentFormat::class,
        'status' => DartTournamentStatus::class,
        'game_mode' => DartGameType::class,
        'game_options' => AsCollection::class,
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'dart_tournament_participants')
            ->withPivot('seed_position', 'final_placement', 'eliminated', 'wins', 'losses', 'points_for', 'points_against')
            ->withTimestamps();
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(DartTournamentRound::class)->orderBy('round_number', 'ASC');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(DartTournamentMatch::class);
    }

    public function getGameOption(string $key, mixed $default = null): mixed
    {
        if ($this->game_options === null) {
            return $default;
        }
        return $this->game_options->get($key, $default);
    }

    public function isSingleElimination(): bool
    {
        return $this->format === DartTournamentFormat::SINGLE_ELIMINATION;
    }

    public function isRoundRobin(): bool
    {
        return $this->format === DartTournamentFormat::ROUND_ROBIN;
    }
}
