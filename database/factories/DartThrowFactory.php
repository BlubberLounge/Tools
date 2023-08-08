<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Enums\DartRingType;
use App\Models\DartGame;
use App\Models\User;
use PhpParser\Node\Expr\Cast\Double;

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
        return array_merge(
            [
                'user_id' => User::inRandomOrder()->first('id'),
                'dart_game_id' => DartGame::inRandomOrder()->first('id'),
                'set' => 1,
                'leg' => 1,
                'turn' => 1,
                'throw' => 1,
                'origin_type' => 'DARTBOARD'
            ],
            $this->getValueFieldRingDefinition(),
        );
    }

    /**
     *
     */
    protected function getValueFieldRingDefinition(): array
    {
        $ring = fake()->randomElement(DartRingType::cases());
        $ringName = $ring->name;
        $theta = 0;
        $r = 0;

        if($ringName == 'O') {
            $multiplier = 0;
            $r = fake()->randomFloat(4, 100-13, 100);
        } else if($ringName == 'S') {
            $multiplier = 1;
            $r = fake()->boolean() ? fake()->randomFloat(4, 6+7, 6+7+32) : fake()->randomFloat(4, 6+7+32+8, 6+7+32+8+29);
        } else if($ringName == 'D') {
            $multiplier = 2;
            $r = fake()->randomFloat(4, 6+7+32+8+29, 6+7+32+8+29+9);
        } else if($ringName == 'T') {
            $multiplier = 3;
            $r = fake()->randomFloat(4, 6+7+32, 6+7+32+8);
        }

        $fields = [20, 1, 18, 4, 13, 6, 10, 15, 2, 17, 3, 19, 7, 16, 8, 11, 14, 9 ,12, 5];
        // $fields[] = 25;
        $field = fake()->randomElement($fields);
        $fieldSize = (360 / 20);
        $fieldHalf = $fieldSize / 2;
        $fieldIndex = array_search($field, $fields);
        $fieldStart = ($fieldIndex * $fieldSize) - $fieldHalf;
        $fieldEnd = $fieldStart + $fieldSize;
        $theta = $fieldIndex == 0 ? (fake()->boolean() ? fake()->randomFloat(4, 351, 360) : fake()->randomFloat(4, 0, 9)) : fake()->randomFloat(4, $fieldStart, $fieldEnd);

        $polar2cartesian = function(float $radius, float $angle, bool $radians = false): array
        {
            $x = $radius * cos($radians ? $angle : deg2rad($angle));
            $y = $radius * sin($radians ? $angle : deg2rad($angle));
            return [
                'x' => $x,
                'y' => $y,
            ];
        };

        $cartesian2polar = function(float $x, float $y): array
        {
            $r = sqrt($x*$x + $y*$y);
            $theta = rad2deg(atan2($y, $x));
            return [
                'radius' => $r,
                'theta' => $theta < 0 ? $theta + 360 : $theta,
            ];
        };

        $normalise = function(int|float $val): float
        {
            return $val / 100; // ($x - $in_min) * ($out_max - $out_min) / ($in_max - $in_min) + $out_min;
        };

        $coords = $polar2cartesian($normalise($r), $theta - 90);
        // dd(array_merge(
        //     [
        //         'test' => $r*cos($theta),
        //         'field' => $field,
        //         'index' => $fieldIndex,
        //         'fieldStart' => $fieldStart,
        //         'fieldEnd' => $fieldEnd,
        //         'rawRadius' => $r,
        //         'rawAngle' => $theta
        //     ],
        //      $coords,
        //      $cartesian2polar($coords['x'], $coords['y']),
        // ));

        return [
            'value' => $field * $multiplier,
            'field' => strval($field), // Enum
            'ring' => $ring,
            'x' => $fieldIndex+1 == end($fields) ? 0 : $coords['x'],
            'y' => $fieldIndex+1 == end($fields) ? 0 : $coords['y'] * -1,
        ];
    }
}
