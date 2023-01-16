<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Role extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /*
     * Define role IDs here 
     */
    const ADMIN = 1;
    const USER = 2;
    const GUEST = 3;


    /**
     * Get users with this role.
     */
    public function user() 
    {
        return $this->hasMany(User::class);
    }
}
