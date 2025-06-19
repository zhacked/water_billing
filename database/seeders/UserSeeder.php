<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'address' => '123 Admin Lane',
                'contact_number' => '1234567890',
                'status' => 'active',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'remember_token' => Str::random(10),
            ],
            [
                'account_id' => 'A18599963662',
                'name' => 'Client User',
                'email' => 'client@example.com',
                'address' => '456 Client Blvd',
                'contact_number' => '0987654321',
                'status' => 'active',
                'meter_number' => '1234',
                'group_id' => 1,
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'client',
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@example.com',
                'address' => '789 Staff Ave',
                'contact_number' => '1122334455',
                'status' => 'active',
                'email_verified_at' => now(),
                'group_id' => 1,
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'remember_token' => Str::random(10),
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $user->assignRole($role);
        }
    }
}
