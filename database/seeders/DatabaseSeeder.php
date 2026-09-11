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
        // User::factory(10)->create();

        // Seed admin user if not exists
        User::firstOrCreate(
            ['email' => 'admin@banksulteng.co.id'],
            [
                'name' => 'Administrator Bank Sulteng',
                'password' => bcrypt('password'),
            ]
        );

        $this->call([
            TerminalSeeder::class,
        ]);
    }
}
