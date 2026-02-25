<?php

namespace Database\Seeders;

use App\Models\User;
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
        // Seed roles and permissions first
        $this->call(RolePermissionSeeder::class);

        // Create test users with roles
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_active' => true,
        ]);
        $admin->assignRole('Admin');

        $teacher = User::factory()->create([
            'name' => 'Teacher User',
            'email' => 'teacher@example.com',
            'is_active' => true,
        ]);
        $teacher->assignRole('Teacher');

        $student = User::factory()->create([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'is_active' => true,
        ]);
        $student->assignRole('Student');
    }
}
