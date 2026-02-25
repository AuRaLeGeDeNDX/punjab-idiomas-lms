<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
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
            // User management
            'manage users',
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Course management
            'manage courses',
            'view courses',
            'create courses',
            'edit courses',
            'delete courses',
            'publish courses',
            
            // Content management
            'manage content',
            'upload files',
            'delete files',
            
            // Assessment management
            'manage assessments',
            'create assignments',
            'grade assignments',
            'view grades',
            
            // Student specific
            'enroll courses',
            'submit assignments',
            'view own grades',
            'access course content',
            
            // Communication
            'send announcements',
            'manage forums',
            'send messages',
            
            // System administration
            'manage system',
            'view reports',
            'manage settings',
            'backup system',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Admin role - full system access
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions(Permission::all());

        // Teacher role - course and assessment management
        $teacherRole = Role::firstOrCreate(['name' => 'Teacher']);
        $teacherRole->syncPermissions([
            'view users',
            'manage courses',
            'view courses',
            'create courses',
            'edit courses',
            'publish courses',
            'manage content',
            'upload files',
            'manage assessments',
            'create assignments',
            'grade assignments',
            'view grades',
            'send announcements',
            'manage forums',
            'send messages',
        ]);

        // Student role - learning and participation
        $studentRole = Role::firstOrCreate(['name' => 'Student']);
        $studentRole->syncPermissions([
            'view courses',
            'enroll courses',
            'submit assignments',
            'view own grades',
            'access course content',
            'send messages',
        ]);
    }
}
