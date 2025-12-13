<?php

namespace Database\Seeders;

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
        // Seed default categories
        $this->call(KategoriKontenSeeder::class);
        
        // Seed default admin and operator users
        $this->call(AdminUserSeeder::class);
    }
}
