<?php

namespace Database\Seeders;

use App\Models\PowerNodeRelation;
use App\Models\PowerNode;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        User::factory()->create([
            'email' => 'test@t.com',
        ]);

        $nodes = PowerNode::factory()->count(20)->create();

        $nodes->skip(1)->each(function ($node) use ($nodes) {
            $possibleParent = $nodes->where('id', '!=', $node->id)->random();

            PowerNodeRelation::factory()->create([
                'node_id' => $node->id,
                'parent_node_id' => $possibleParent->id,
            ]);
        });
    }
}
