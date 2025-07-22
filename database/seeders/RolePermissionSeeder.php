<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Optional: clear existing permissions and roles for a clean slate
        // DB::table('role_has_permissions')->truncate();
        // Permission::truncate();
        // Role::truncate();

        // Define permissions
        $permissions = [
            'shared-access',
            'admin-only',
            'client-only',
            'plumbing-only',
            'cashier-only',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create and assign permissions to roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions); // all permissions

        $client = Role::firstOrCreate(['name' => 'client']);
        $client->syncPermissions([
            'client-only',
        ]);

        $plumber = Role::firstOrCreate(['name' => 'plumber']);
        $plumber->syncPermissions([
            'plumbing-only',
            'shared-access'
        ]);

        $cashier = Role::firstOrCreate(['name' => 'cashier']);
        $cashier->syncPermissions([
            'cashier-only',
            'shared-access'
        ]);

        $this->command->info('✅ Roles and permissions seeded successfully.');
    }
}
