<?php

namespace Database\Factories;

use App\Models\PowerNodeRelation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PowerNodeRelation>
 */
class PowerNodeRelationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'node_id' => $this->faker->uuid(),
            'parent_node_id' => $this->faker->uuid(),
        ];
    }
}
