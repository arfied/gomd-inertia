<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create business_admin role
        $businessAdminRole = Role::create(['name' => 'business_admin', 'guard_name' => 'web']);
        $businessHrRole = Role::create(['name' => 'business_hr', 'guard_name' => 'web']);
        $employeeRole = Role::create(['name' => 'employee', 'guard_name' => 'web']);
        $agentRole = Role::create(['name' => 'agent', 'guard_name' => 'web']);

        // Create permissions for business management
        $permissions = [
            'manage business',
            'view business',
            'edit business',
            'manage business employees',
            'manage business plans',
            'view business dashboard',
        ];

        // Create the permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign all business permissions to business_admin role
        $businessAdminRole->syncPermissions($permissions);
        $businessHrRole->syncPermissions($permissions);

        // Also give admin role these permissions if it exists
        try {
            $adminRole = Role::findByName('admin', 'web');
            $adminRole->givePermissionTo($permissions);
        } catch (\Exception $e) {
            // Admin role doesn't exist yet, create it
            $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
            $adminRole->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the role
        $role = Role::findByName('business_admin', 'web');
        if ($role) {
            $role->delete();
        }

        // Delete the permissions
        $permissions = [
            'manage business',
            'view business',
            'edit business',
            'manage business employees',
            'manage business plans',
            'view business dashboard',
        ];

        foreach ($permissions as $permission) {
            $permissionModel = Permission::findByName($permission, 'web');
            if ($permissionModel) {
                $permissionModel->delete();
            }
        }
    }
};
