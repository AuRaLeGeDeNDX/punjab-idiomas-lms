<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserTrashManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Student']);
        
        // Create admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
    }

    public function test_admin_can_soft_delete_user()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user));

        $response->assertRedirect();
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_can_view_trashed_users()
    {
        $user = User::factory()->create();
        $user->delete();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.trashed'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.trashed');
        $response->assertSee($user->name);
    }

    public function test_admin_can_restore_user()
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $user->id]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.restore', $user->id));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_force_delete_user()
    {
        $user = User::factory()->create();
        $user->delete();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.force-delete', $user->id));

        $response->assertRedirect(route('admin.users.trashed'));
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_can_empty_trash()
    {
        $users = User::factory()->count(3)->create();
        $users->each->delete();

        $this->assertEquals(3, User::onlyTrashed()->count());

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.empty-trash'));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertEquals(0, User::onlyTrashed()->count());
    }
}
