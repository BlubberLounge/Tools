<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A nether portal a user saved in the Minecraft portal calculator.
 * One row per portal and dimension; x/y/z is one block of the portal.
 */
class MinecraftPortal extends Model
{
    public const DIMENSIONS = ['overworld', 'nether'];

    protected $fillable = [
        'user_id',
        'name',
        'world',
        'dimension',
        'x',
        'y',
        'z',
    ];

    protected $casts = [
        'x' => 'integer',
        'y' => 'integer',
        'z' => 'integer',
    ];

    protected $hidden = [
        'user_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
