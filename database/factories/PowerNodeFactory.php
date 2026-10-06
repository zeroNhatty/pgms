<?php

namespace Database\Factories;

use App\Models\PowerNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PowerNode>
 */
class PowerNodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "location" =>
                $this->faker->longitude() . ", " . $this->faker->latitude(),
            "status" => $this->faker->randomElement(["active"]),
        ];
    }
}
