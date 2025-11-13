<?php

namespace App\Models;

use App\Enums\CocktailStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use OwenIt\Auditing\Contracts\Auditable;
use App\Traits\Rateable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cocktail extends Model implements Auditable
{
    /** @use HasFactory<\Database\Factories\CocktailFactory> */
    use HasFactory,
        SoftDeletes,
        Rateable,
        \OwenIt\Auditing\Auditable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        '*'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CocktailStatus::class
        ];
    }

    protected static function booted()
    {
        static::saving(function ($cocktail) {
            $cocktail->slug = Str::slug($cocktail->name);
            if(Auth::check())
                $cocktail->created_by = Auth::id();
        });

        static::creating(function ($cocktail) {
            if(Auth::check())
                $cocktail->created_by = Auth::id();
        });
    }

    /**
     * Get the creator
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the recipes
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * Get the main recipe
     */
    public function getRecipe(): Recipe
    {
        return $this->recipes()->first();
    }

    /**
     * Scope a query with only cocktail with images
     */
    public function scopeWithImage(Builder $query): void
    {
        $query->whereNot('image', null);
    }
}
