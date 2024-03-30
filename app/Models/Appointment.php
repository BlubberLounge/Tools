<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use OwenIt\Auditing\Contracts\Auditable;

class Appointment extends Model implements Auditable
{
    use HasFactory,
        \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'start',
        'end',
        'title',
        'description',
        'user_id'
    ];

    /**
     *
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('status');
    }
}