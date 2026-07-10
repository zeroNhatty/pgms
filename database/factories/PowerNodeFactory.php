<?php

namespace Database\Factories;

use App\Models\PowerNodes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PowerNodes>
 */
class PowerNodesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location' => $this->faker->address(),
            'status' => $this->faker->randomElement(["active", "inactive", "being_maintained"]),
        ];
    }
}
