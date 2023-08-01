<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Enums\DartRingType;

class DartThrow extends Model
{
    use HasFactory;

        /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'set',
        'leg',
        'turn',
        'throw',
        'value',
        'field',
        'ring',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
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
