<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default Admin user
        User::firstOrCreate(
            ['email' => 'admin@tanbu.go.id'],
            [
                'name' => 'Administrator',
                'email' => 'admin@tanbu.go.id',
                'password' => Hash::make('admin123'),
                'role' => 'Admin',
                'skpd_id' => null,
                'is_active' => true,
            ]
        );

        // Create default Operator user
        User::firstOrCreate(
            ['email' => 'operator@tanbu.go.id'],
            [
                'name' => 'Operator Diskominfo',
                'email' => 'operator@tanbu.go.id',
                'password' => Hash::make('operator123'),
                'role' => 'Operator',
                'skpd_id' => null,
                'is_active' => true,
            ]
        );
    }
}
