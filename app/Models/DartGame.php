<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Enums\DartGameType;
use App\Enums\DartGameStatus;

class DartGame extends Model
{
    use HasFactory,
        SoftDeletes,
        HasUuids;

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
     * The users that belong to the DartGame.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Get all of the throws for the project.
     */
    public function dartThrows(): HasMany
    {
        return $this->hasMany(DartThrow::class);
    }
}
