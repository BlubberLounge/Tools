<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

use App\Enums\DartRingType;

class DartThrow extends Model implements Auditable
{
    use HasFactory,
        SoftDeletes,
        \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'set',
        'leg',
        'turn',
        'throw',
        'value',
        'field',
        'ring',
        'x',
        'y',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'ring' => DartRingType::class,
    ];

    /**
     * Get all of the posts for the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all of the posts for the user.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(DartGame::class);
    }
}
