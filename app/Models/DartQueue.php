<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable;


class DartQueue extends Model implements Auditable
{
    use HasFactory,
        \OwenIt\Auditing\Auditable;

    /**
    * The table associated with the model.
    */
   protected $table = 'dart_queue';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_user_id',
        'child_user_id',
        'position',
    ];

    /**
     *
     */
    public function parentUser(): hasOne
    {
        return $this->hasOne(User::class, 'id', 'parent_user_id')
            ->orderBy('created_at', 'DESC');
    }

    // /**
    //  *
    //  */
    // public function childUsers(): HasMany
    // {
    //     return $this->hasMany(User::class, 'child_user_id');
    // }
}
