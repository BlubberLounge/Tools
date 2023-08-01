<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Enums\DartRingType;
use App\Models\DartGame;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DartThrows>
 */
class DartThrowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return array_merge([
            'user_id' => User::inRandomOrder()->first('id'),
            'dart_game_id' => DartGame::inRandomOrder()->first('id'),
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
        ], $this->getValueFieldRingDefinition());
    }

    /**
     *
     */
    protected function getValueFieldRingDefinition(): array
    {
        $ring = fake()->randomElement(DartRingType::cases());
        $ringName = $ring->name;

        if($ringName == 'S') {
            $multiplier = 1;
        } else if($ringName == 'D') {
            $multiplier = 2;
        } else if($ringName == 'T') {
            $multiplier = 3;
        }

        $fields = range(0, 20, 1);
        $fields[] = 25;
        $field = fake()->randomElement($fields);

        return [
            'value' => $field * $multiplier,
            'field' => strval($field), // Enum
            'ring' => $ring,
        ];
    }
}
