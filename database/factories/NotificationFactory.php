<?php

namespace Database\Factories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\User;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'channel' => fake()->randomElement(NotificationChannel::cases()),
            'status' => NotificationStatus::Processing,
            'message' => fake()->text(200),
            'attempts' => 0,
            'last_error' => null,
            'last_attempt_at' => null,
            'delivered_at' => null,
        ];
    }
}
