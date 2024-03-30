<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\User;
use App\Models\Acquaintance;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Acquaintance>
 */
class AcquaintanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $this->faker->locale('de_DE');

        return [
            'transmitter_user_id' => fake()->unique()->numberBetween(2, User::all()->count()-1),
            'receiver_user_id' => User::rootAcc()->id,
            'status' => fake()->boolean(55),
        ];
    }

    /**
     * get on overlapping user id
     *
     */
    private function getUniqueUserId() {
        // User::whereNotIn('id', User::adminAcc()->acquaintances->pluck('id')->all())->get()->random()->id
        /* do {
            $id = User::all()->random()->id;
        } while(Acquaintance::where('transmitter_user_id', $id)->where('receiver_user_id', User::adminAcc()->id)->exists());
        return $id;
        */
    }
}
