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
use Illuminate\Support\Facades\DB;

use App\Enums\DartGameType;
use App\Enums\DartGameStatus;

class DartGame extends Model
{
    use HasFactory,
        SoftDeletes,
        HasUuids;

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

        // Generall / X01
        'singleOut',
        'doubleOut',
        'trippleOut',
        'singleIn',
        'doubleIn',
        'trippleIn',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => DartGameType::class,
        'status' => DartGameStatus::class,
        'fields' => AsCollection::class,
    ];


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
    public function scopeOpen(Builder $query): void
    {
        $query->where($this->getTable().'.status', DartGameStatus::UNKOWN)
            ->orWhere($this->getTable().'.status', DartGameStatus::CREATED);
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
            ->withPivot('status', 'place')
            ->orderBy('place', 'ASC')
            ->withTimestamps();
    }

    /**
     * Get all of the throws for the project.
     */
    public function dartThrows(): HasMany
    {
        return $this->hasMany(DartThrow::class);
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
        $DBresult = DB::table($this->getTable())
            ->selectRaw('user_id, field, ring, CAST(streak AS UNSIGNED) as streak')
            ->from(function($q) {
                return $q   // Ugly but very fast Sub-Query to find the highest Streak
                    ->selectRaw(DB::raw('
                    t.id, t.dart_game_id, t.user_id, t.deleted_at, t.ring, t.field,
                    @streak := IF(field = @last_field AND ring = @last_ring, @streak+1, 1) AS streak, @last_field := field AS last_field, @last_ring := t.ring AS last_ring
                    FROM devt_dart_throws t
                    JOIN (SELECT @streak := 0, @last_field := "0" COLLATE utf8mb4_unicode_ci) j
                    WHERE dart_game_id = "'.$this->id.'" AND deleted_at IS NULL
                    ORDER BY user_id ASC'
                ));
            })
            ->groupBy('user_id', 'ring', 'field', 'streak')
            // ->join('users', 'users.id', '=', 'user_id')
            ->get();

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
}
