<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update Admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        // Create or update Teacher user
        $teacher = User::updateOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Teacher User',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        if (!$teacher->hasRole('Teacher')) {
            $teacher->assignRole('Teacher');
        }

        // Create or update Student user
        $student = User::updateOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Student User',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        if (!$student->hasRole('Student')) {
            $student->assignRole('Student');
        }

        echo "Test users created/updated:\n";
        echo "Admin: admin@example.com / password\n";
        echo "Teacher: teacher@example.com / password\n";
        echo "Student: student@example.com / password\n";
    }
}