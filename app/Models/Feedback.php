<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// composer package traits
use OwenIt\Auditing\Contracts\Auditable;

use App\Enums\FeedbackType;
use App\Enums\FeedbackStatus;


class Feedback extends Model implements Auditable
{
    use HasFactory,
        SoftDeletes,
        \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'status',
        'subject',
        'message',
        'area',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => FeedbackType::class,
        'status' => FeedbackStatus::class,
    ];

    /**
     * Get the user that owns this feedback
     */
    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the device that corresponds to this feedback / user
     */
    public function device(): belongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
