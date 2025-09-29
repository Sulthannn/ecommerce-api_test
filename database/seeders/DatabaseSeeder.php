<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Pengguna Demo',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        if (Product::count() === 0) {
            Product::factory()->count(15)->create();
        }
    }
}
