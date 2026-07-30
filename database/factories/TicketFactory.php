<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => fake()->randomElement(['solved', 'pending', 'assigned']),
            'node_id' => $this->faker->uuid(),
            'assignee_id' => $this->faker->uuid(),
        ];
    }
}
