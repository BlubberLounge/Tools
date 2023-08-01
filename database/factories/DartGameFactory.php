<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Classes\Utillity;
use App\Enums\DartGameType;
use App\Enums\DartGameStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DartGame>
 */
class DartGameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()//: array
    {
        $this->faker->locale('de_DE');

        $type = Utillity::getRandomWeightedElement([
            'X01' => 75, 'ATC' => 15, 'cricket' => 10
        ]);
        $definition = null;

        if($type === 'X01') {
            $definition = $this->getX01Definition();
        } else if($type === 'ATC') {
            $definition = $this->getAroundTheClockDefinition();
        } else if($type === 'cricket') {
            $definition = $this->getCricketDefinition();
        }

        return array_merge($definition, $this->commomDefinitions());
    }

    /**
     *
     */
    private function commomDefinitions()
    {
        return [
            'status' => $this->getWeigthedStatus(),
            'private' => fake()->boolean(5),
            'title' => fake()->sentence(5),
            'comment' => fake()->text(),
            'singleOut' => fake()->boolean(90),
            'doubleOut' => fake()->boolean(90),
            'trippleOut' => fake()->boolean(90),
            'singleIn' => fake()->boolean(90),
            'doubleIn' => fake()->boolean(90),
            'trippleIn' => fake()->boolean(90),
        ];
    }


    /**
     *
     */
    protected function getX01Definition(): array
    {
        return [
            'type' => DartGameType::X01,
            'points' => Utillity::getRandomWeightedElement([301 => 50, 401 => 10, 501 => 10, 601 => 10, 701 => 10, 801 => 5, 901 => 5]),
            'start' => null,
            'end' => null,
            'fields' => null,
        ];
    }

    /**
     *
     */
    protected function getAroundTheClockDefinition(): array
    {
        return [
            'type' => DartGameType::aroundTheClock,
            'points' => null,
            'start' => fake()->randomElement([20, 15, 10]),
            'end' => fake()->randomElement([0, 5]),
            'fields' => null,
        ];
    }

    /**
     *
     */
    protected function getCricketDefinition(): array
    {
        return [
            'type' => DartGameType::cricket,
            'points' => null,
            'start' => null,
            'end' => null,
            'fields' => json_encode([15, 16, 17, 18, 19, 20, 'bull']),
        ];
    }

    /**
     *
     *
     */
    public function getWeigthedStatus(): DartGameStatus
    {
        $status = Utillity::getRandomWeightedElement([
            'unkown' => 15,
            'created' => 5,
            'started' => 10,
            'running' => 8,
            'done' => 50,
            'aborted' => 10,
            'error' => 2,
        ]);

        return DartGameStatus::fromString($status);
    }
}
