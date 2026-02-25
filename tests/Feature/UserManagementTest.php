<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Teacher']);
        Role::create(['name' => 'Student']);
        
        // Create admin user
        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'is_active' => true,
        ]);
        $this->admin->assignRole('Admin');
    }

    public function test_admin_can_access_user_index()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
        $response->assertViewHas('roles');
    }

    public function test_admin_can_access_user_create_form()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create');
        $response->assertViewHas('roles');
    }

    public function test_admin_can_create_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Student',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_user_details()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.show');
        $response->assertViewHas('user');
    }

    public function test_admin_can_edit_user()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $user));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.edit');
        $response->assertViewHas('user');
        $response->assertViewHas('roles');
    }

    public function test_admin_can_update_user()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $updateData = [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'Teacher',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_admin_can_toggle_user_status()
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('Student');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.toggle-status', $user));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_user()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user));

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_non_admin_cannot_access_user_management()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');

        $response = $this->actingAs($teacher)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_user_management()
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect(route('login'));
    }
}