<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\User;
use App\Enums\FeedbackType;
use App\Enums\FeedbackStatus;
use App\Http\Controllers\FeedbackController;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $f = new FeedbackController();
        return [
            'user_id' => User::rootAcc()->id,
            'type' => fake()->randomElement(FeedbackType::cases()),
            'status' => fake()->randomElement(FeedbackStatus::cases()),
            'subject' => fake()->sentence(10),
            'message' => fake()->text(),
            'area' => fake()->randomElement($f->areas),
            'device_id' => fake()->randomElement(User::rootAcc()->devices),
        ];
    }
}
