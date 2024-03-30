<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Acquaintance extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transmitter_user_id',
        'receiver_user_id',
        'status',
        'showOnHomeView',
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    // protected $guarded = [
    //     'id',
    //     'created_at',
    //     'updated_at'
    // ];

}
