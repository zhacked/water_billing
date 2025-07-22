<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Optional: clear users and roles in local/dev
        // User::truncate();

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
            ],
            [
                'account_id' => 'A18599963662',
                'category_id' => 1,
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
            ],
            [
                'name' => 'Plumber',
                'category_id' => 1,
                'email' => 'plumber@example.com',
                'address' => '789 Plumber Ave',
                'contact_number' => '1122334455',
                'status' => 'active',
                'group_id' => 1,
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'plumber',
            ],
            [
                'name' => 'Cashier',
                'category_id' => 1,
                'email' => 'cashier@example.com',
                'address' => '789 Cashier Ave',
                'contact_number' => '1122334455',
                'status' => 'active',
                'group_id' => 1,
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'cashier',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];

            $user = User::updateOrCreate(['email' => $data['email']], $data);

            // Double check the role exists
            $roleModel = Role::firstOrCreate(['name' => $role]);

            // Use syncRoles to avoid duplicates
            $user->syncRoles([$roleModel]);
        }

        $this->command->info('✅ Users created and correct roles assigned.');
    }
}
