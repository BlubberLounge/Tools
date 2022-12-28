<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Manufacturer;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hookah>
 */
class HookahFactory extends Factory
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
            'name' => $this->randomName(),
            'description' => $this->faker->sentence($this->faker->numberBetween(10, 25)),
            'manufacturer_id' => Manufacturer::all()->random()->id,
        ];
    }

    /**
     * Generate a random Hookah name
     * 
     * @return string
     */
    private function randomName()
    {
        $shuffledName = $this->faker->shuffle($this->faker->word());
        $prefix = $this->faker->randomElement(['Hookah', 'Shisha', 'Shiiiiish', 'Sauger', 'Rauchgerät', $shuffledName, $shuffledName]);
        $con = $this->faker->randomElement(['#', '-', '.', ' ', '/', '']);
        $suffix = $this->faker->numberBetween(0, 9999);

        return $this->faker->boolean(55)
            ? $prefix.$con.$suffix
            : $prefix.' by '. $this->faker->name();
    }
}
