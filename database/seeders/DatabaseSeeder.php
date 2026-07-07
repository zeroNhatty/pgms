<?php

namespace Database\Seeders;

use App\Models\PowerNodes;
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
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'role' => 'manager',
        ]);

        User::factory()->create([
            'role' => 'administrator',
        ]);

        PowerNodes::factory(10)->create();
    }
}
