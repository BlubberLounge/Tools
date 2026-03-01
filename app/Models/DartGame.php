<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

use OwenIt\Auditing\Contracts\Auditable;
use App\Enums\DartGameType;
use App\Enums\DartGameStatus;
use App\Models\DartThrow;
use App\Enums\DartGameUserStatus;
use App\Enums\DartPlayType;
use App\Notifications\DartGameStarted;
use Ramsey\Uuid\Type\Integer;

class DartGame extends Model // implements Auditable doesn't work because of uuids af primary
{
    use HasFactory,
        SoftDeletes,
        HasUuids;
        // \OwenIt\Auditing\Auditable doesn't work because of uuids af primary

    /**
     * The data type of the ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'game_type',
        'status',
        'private',
        'title',
        'comment',

        // X01
        'points',

        // Around The Clock
        'start',
        'end',

        // Cricket
        'fields',

        // Mode-specific settings (JSON)
        'settings',

        // Timestamps
        'started_at',
        'finished_at',

        // Generall / X01
        'singleOut',
        'doubleOut',
        'trippleOut',
        'singleIn',
        'doubleIn',
        'trippleIn',

        // Sync
        'client_game_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => DartGameType::class,
        'game_type' => DartPlayType::class,
        'status' => DartGameStatus::class,
        'fields' => AsCollection::class,
        'settings' => AsCollection::class,
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * Attributes to include in the Audit.
     *
     * @var array
     */
    protected $auditInclude = [
        //
    ];


    /**
     *
     */
    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    /**
     *
     */
    public function scopeUnkown(Builder $query): void
    {
        $query->where($this->getTable().'.status', DartGameStatus::UNKOWN);
    }

    /**
     *
     */
    public function scopeCreated(Builder $query): void
    {
        $query->where($this->getTable().'.status', DartGameStatus::CREATED);
    }

    /**
     *
     */
    public function scopeStarted(Builder $query): void
    {
        $query->where($this->getTable().'.status', DartGameStatus::STARTED);
    }

    /**
     *
     */
    public function scopeRunning(Builder $query): void
    {
        $query->where($this->getTable().'.status', DartGameStatus::RUNNING);
    }

    /**
     *
     */
    public function scopeDone(Builder $query): void
    {
        $query->where($this->getTable().'.status', DartGameStatus::DONE);
    }

    /**
     *
     */
    public function scopeAborted(Builder $query): void
    {
        $query->where($this->getTable().'.status', DartGameStatus::ABORTED);
    }

    /**
     *
     */
    public function scopeError(Builder $query): void
    {
        $query->where($this->getTable().'.status', DartGameStatus::ERROR);
    }

    /**
     *
     */
    public function scopeInitialised(Builder $query): void
    {
        $query->where($this->getTable().'.status', DartGameStatus::INITIALISED);
    }

    public function scopePaused(Builder $query): void
    {
        $query->where($this->getTable().'.status', DartGameStatus::PAUSED);
    }

    public function scopeFinished(Builder $query): void
    {
        $query->where($this->getTable().'.status', DartGameStatus::FINISHED);
    }

    public function scopeOpen(Builder $query): void
    {
        $query->where($this->getTable().'.status', DartGameStatus::UNKOWN)
            ->orWhere($this->getTable().'.status', DartGameStatus::CREATED)
            ->orWhere($this->getTable().'.status', DartGameStatus::INITIALISED);
    }

    /**
     * The user that created the DartGame.
     * createdBy
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The users that participate in the DartGame.
     *
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('status', 'position', 'place', 'dart_team_id')
            ->orderBy('position', 'ASC')
            ->wherePivot('deleted_at', null)
            ->withTimestamps();
    }

    /**
     * The users that participate in the DartGame.
     *
     */
    public function usersBy($column, $order = 'asc'): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('status', 'position', 'place', 'dart_team_id')
            ->orderBy($column, $order)
            ->wherePivot('deleted_at', null)
            ->withTimestamps();
    }

    /**
     * Teams in this game (for team mode).
     */
    public function teams(): HasMany
    {
        return $this->hasMany(DartTeam::class)->orderBy('position', 'ASC');
    }

    /**
     * Get a game setting value with a default fallback.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        if ($this->settings === null) {
            return $default;
        }
        return $this->settings->get($key, $default);
    }

    /**
     * Whether this is a team game.
     */
    public function isTeamGame(): bool
    {
        return $this->game_type === DartPlayType::TEAM;
    }

    /**
     * The tournament match this game belongs to (if any).
     */
    public function tournamentMatch(): HasOne
    {
        return $this->hasOne(DartTournamentMatch::class, 'dart_game_id');
    }

    /**
     * Get all of the throws for the game.
     */
    public function dartThrows(): HasMany
    {
        return $this->hasMany(DartThrow::class);
    }

    /**
     * Get all of the throws for the game by user.
     */
    public function dartThrowsByUser(User $user): HasMany
    {
        return $this->hasMany(DartThrow::class)
            ->where('user_id', $user->id);
    }

    /**
     *
     */
    public function currentPointsByUser(User $user): int
    {
        return $this->dartThrowsByUser($user)->sum('value');
    }

    /**
     *
     */
    public function remainingPointsByUser(User $user): int
    {
        return $this->points - $this->dartThrowsByUser($user)->sum('value');
    }

    /**
     *
     */
    public function isUnkown(): bool
    {
        return $this->status === DartGameStatus::UNKOWN;
    }

    /**
     *
     */
    public function isCreated(): bool
    {
        return $this->status === DartGameStatus::CREATED;
    }

    /**
     *
     */
    public function isStarted(): bool
    {
        return $this->status === DartGameStatus::STARTED;
    }

    /**
     *
     */
    public function isRunning(): bool
    {
        return $this->status === DartGameStatus::RUNNING;
    }

    /**
     *
     */
    public function isDone(): bool
    {
        return $this->status === DartGameStatus::DONE;
    }

    /**
     *
     */
    public function isAborted(): bool
    {
        return $this->status === DartGameStatus::ABORTED;
    }

    public function isInitialised(): bool
    {
        return $this->status === DartGameStatus::INITIALISED;
    }

    public function isPaused(): bool
    {
        return $this->status === DartGameStatus::PAUSED;
    }

    public function isPlayerWon(): bool
    {
        return $this->status === DartGameStatus::PLAYER_WON;
    }

    public function isFinished(): bool
    {
        return $this->status === DartGameStatus::FINISHED;
    }

    /**
     *
     */
    public function allPlayersAccepted(): bool
    {
        foreach($this->users as $user)
            if($user->pivot->status === DartGameUserStatus::PENDING->value || $user->pivot->status === DartGameUserStatus::DENIED->value)
                return false;

        return true;
    }

    /**
     *
     */
    public function getNumberOfPlayers()
    {
        return $this->users()->count();
    }

    /**
     *
     */
    public function getHighestThrowOfTurn()
    {
        $throws = $this->dartThrows()
            ->with('user')
            ->get();
        $max = $throws->max('value');

        return $throws->firstWhere('value', $max);
    }

    /**
     *
     */
    public function getLowestThrowOfTurn()
    {
        $throws = $this->dartThrows()
            ->with('user')
            ->get();
        $min = $throws->min('value');

        return $throws->firstWhere('value', $min);
    }

    /**
     *
     */
    public function getThrowTurnSums()
    {
        return $this->dartThrows()
            ->select('user_id', 'turn', DB::raw('SUM(value) as turn_total'))
            ->groupBy('user_id', 'turn')
            ->with('user');
    }

    /**
     *
     */
    public function getUserHighestTurn()
    {
        $throws = $this->getThrowTurnSums()->get();
        $max = $throws->max('turn_total');

        return $throws->firstWhere('turn_total', $max);
    }

    /**
     *
     */
    public function getUserLowestTurn()
    {
        $throws = $this->getThrowTurnSums()->get();
        $min = $throws->min('turn_total');

        return $throws->firstWhere('turn_total', $min);
    }

    /**
     *
     */
    public function getLongestStreak()
    {
        $throwsTable = (new DartThrow)->getTable();
        $gameId = $this->id;

        // Use parameterized query to prevent SQL injection
        $DBresult = DB::select('
            SELECT user_id, field, ring, CAST(streak AS UNSIGNED) as streak
            FROM (
                SELECT t.id, t.dart_game_id, t.user_id, t.deleted_at, t.ring, t.field,
                    @streak := IF(field = @last_field AND ring = @last_ring, @streak+1, 1) AS streak,
                    @last_field := field AS last_field,
                    @last_ring := t.ring AS last_ring
                FROM ' . $throwsTable . ' t
                JOIN (SELECT @streak := 0, @last_field := "0" COLLATE utf8mb4_unicode_ci) j
                WHERE dart_game_id = ? AND deleted_at IS NULL AND t.user_id IS NOT NULL
                ORDER BY user_id ASC
            ) sub
            GROUP BY user_id, ring, field, streak
        ', [$gameId]);

        $DBresult = collect($DBresult);

        $max = $DBresult->max('streak');
        $result = $DBresult->firstWhere('streak', $max);

        $arr = [];
        $arr['streakCount'] = $result->streak;
        $arr['field'] = $result->ring . $result->field;
        $arr['user'] = User::find($result->user_id);

        return collect($arr);
    }

    /**
     *
     */
    public function getMostMisthrows()
    {
        $DBresult = $this->dartThrows()
            ->selectRaw('user_id, COUNT(*) as count')
            ->whereNotNull('user_id')
            ->where('ring', 'O')
            ->groupBy('user_id')
            ->get();

        $max = $DBresult->max('count');
        $result = $DBresult->firstWhere('count', $max);

        $arr = [];
        $arr['misthrowCount'] = $result ? $result->count : '/';
        $arr['user'] = $result ? User::find($result->user_id) : null;

        return collect($arr);
    }

    /**
     * Get the last throw of the game for a specific user.
     */
    public function getLastThrowByUser(User $user)
    {
        return $this->dartThrowsByUser($user)
            ->withTrashed()
            ->orderBy('set', 'DESC')
            ->orderBy('leg', 'DESC')
            ->orderBy('turn', 'DESC')
            ->orderBy('throw', 'DESC')
            ->first();
    }

    /**
     *
     */
    public function notifyAllPlayersGameStarted()
    {
        foreach($this->users as $user)
            $user->notify(new DartGameStarted($user, $this->id));
    }
}
