<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Admin permissions
            'admin.modules.manage',
            'admin.users.manage',
            'admin.roles.manage',
            'admin.settings.manage',

            // Global permissions
            'view_any_record',
            'edit_any_record',
            'delete_any_record',

            // Module-specific permissions will be created dynamically
            // Format: modules.{module_api_name}.view
            //         modules.{module_api_name}.create
            //         modules.{module_api_name}.edit
            //         modules.{module_api_name}.delete
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create default module permissions for common modules
        $defaultModules = ['contacts', 'leads', 'accounts', 'opportunities'];
        foreach ($defaultModules as $module) {
            Permission::create(['name' => "modules.{$module}.view"]);
            Permission::create(['name' => "modules.{$module}.create"]);
            Permission::create(['name' => "modules.{$module}.edit"]);
            Permission::create(['name' => "modules.{$module}.delete"]);
        }

        // Create roles
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $admin = Role::create(['name' => 'Admin']);
        $manager = Role::create(['name' => 'Manager']);
        $user = Role::create(['name' => 'User']);

        // Assign permissions to roles
        // Super Admin gets all permissions
        $superAdmin->givePermissionTo(Permission::all());

        // Admin gets most permissions except super admin functions
        $admin->givePermissionTo([
            'admin.modules.manage',
            'admin.users.manage',
            'admin.settings.manage',
            'view_any_record',
            'edit_any_record',
            'delete_any_record',
        ]);

        // Add module permissions to admin
        foreach ($defaultModules as $module) {
            $admin->givePermissionTo([
                "modules.{$module}.view",
                "modules.{$module}.create",
                "modules.{$module}.edit",
                "modules.{$module}.delete",
            ]);
        }

        // Manager gets view, create, edit (no delete, no admin)
        $manager->givePermissionTo(['view_any_record', 'edit_any_record']);
        foreach ($defaultModules as $module) {
            $manager->givePermissionTo([
                "modules.{$module}.view",
                "modules.{$module}.create",
                "modules.{$module}.edit",
            ]);
        }

        // User gets view and create own records only
        foreach ($defaultModules as $module) {
            $user->givePermissionTo([
                "modules.{$module}.view",
                "modules.{$module}.create",
            ]);
        }
    }
}
