<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'location' => fake()->city(),
            'event_date' => fake()->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d'),
            'event_time' => fake()->time('H:i'),
            'max_participants' => fake()->numberBetween(10, 200),
        ];
    }
}
