<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Manufacturer>
 */
class ManufacturerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $this->faker->locale('de_DE');

        return [
            'name' => $this->faker->name() . ' GmbH',
            'description' => $this->faker->sentence($this->faker->numberBetween(10, 25)),
        ];
    }
}
