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
        // Create or update admin user
        $admin = User::updateOrCreate(
            ['email' => 'developers@ecareinfoway.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin@123'),
                'email_verified_at' => now(),
                'role' => 'super_admin',
            ]
        );

        // Ensure super admin has access to all websites (via relationship, super admin doesn't need explicit assignments)

        $this->command->info('Admin user created/updated: developers@ecareinfoway.com / admin@123');
    }
}
