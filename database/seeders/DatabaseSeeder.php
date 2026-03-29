<?php

namespace Database\Seeders;

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
        User::factory()->create([
            'name' => 'LGU User',
            'email' => 'lgu@example.com',
            'role' => 'LGU',
        ]);

        User::factory()->create([
            'name' => 'Barangay Captain User',
            'email' => 'brgcaptain@example.com',
            'role' => 'BrgCaptain',
        ]);

        User::factory()->create([
            'name' => 'BNS User',
            'email' => 'bns@example.com',
            'role' => 'BNS',
        ]);

        User::factory()->create([
            'name' => 'BHW User',
            'email' => 'bhw@example.com',
            'role' => 'BHW',
        ]);

        User::factory()->create([
            'name' => 'Users Role User',
            'email' => 'users@example.com',
            'role' => 'users',
        ]);
    }
}
