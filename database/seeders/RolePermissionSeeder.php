<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            'access dashboard',
            'admin section',
            'client section',
            'staff section',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles and assign existing permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions); // all permissions

        $client = Role::firstOrCreate(['name' => 'client']);
        $client->syncPermissions([
            'access dashboard',
            'client section',
        ]);

        $plumber = Role::firstOrCreate(['name' => 'plumber']);
        $plumber->syncPermissions([
            'access dashboard',
            'staff section',
        ]);

        $cashier = Role::firstOrCreate(['name' => 'cashier']);
        $cashier->syncPermissions([
            'access dashboard',
            'staff section',
        ]);
    }
}
