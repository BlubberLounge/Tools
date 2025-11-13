<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

class Recipe extends Model implements Auditable
{
    /** @use HasFactory<\Database\Factories\RecipeFactory> */
    use HasFactory,
        SoftDeletes,
        \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'order',
        'created_by'
    ];

    protected static function booted()
    {
        static::saving(function ($recipe) {
            if(Auth::check())
                $recipe->created_by = Auth::id();
        });

        static::creating(function ($recipe) {
            if(Auth::check())
                $recipe->created_by = Auth::id();
        });
    }

    /**
     * Get the cocktail
     */
    public function cocktail(): BelongsTo
    {
        return $this->belongsTo(Cocktail::class);
    }

    /**
     * Get the ingredients
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }
}
