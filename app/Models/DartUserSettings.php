<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DartUserSettings extends Model
{
    protected $table = 'dart_user_settings';

    protected $fillable = [
        'user_id',
        'settings',
    ];

    protected $casts = [
        'settings' => AsCollection::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
