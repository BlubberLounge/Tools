<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

use App\Enums\Units;
use App\Services\UnitConverterService;


class Ingredient extends Model implements Auditable
{
    /** @use HasFactory<\Database\Factories\IngredientFactory> */
    use HasFactory,
        SoftDeletes,
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
            'unit_of_measurement' => Units::class
        ];
    }

    /**
     * Get the recipe
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function convertQuantity(Units $toUnit): float
    {
        return UnitConverterService::convert($this->quantity, Units::from($this->unit_of_measurement), $toUnit);
    }
}
