<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use App\Modules\Identity\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function seedPermissions(): void
    {
        foreach ([
            'identity.manage',
            'identity.user.view',
            'identity.user.create',
            'identity.user.update',
        ] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());
    }

    public function test_user_without_role_cannot_access_user_management(): void
    {
        $this->seedPermissions();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('identity.users.index'));

        $response->assertForbidden();
    }

    public function test_super_admin_can_create_user_and_assign_role(): void
    {
        $this->seedPermissions();

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $financeRole = Role::create(['name' => 'Finance', 'guard_name' => 'web']);

        $response = $this->actingAs($superAdmin)->post(route('identity.users.store'), [
            'name' => 'Staff Finance',
            'email' => 'staff.finance@test.local',
            'password' => 'password123',
            'roles' => [$financeRole->name],
            'is_active' => true,
        ]);

        $response->assertRedirect(route('identity.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'staff.finance@test.local',
        ]);

        $newUser = User::where('email', 'staff.finance@test.local')->first();

        $this->assertTrue($newUser->hasRole('Finance'));
    }

    public function test_audit_log_recorded_when_user_updated(): void
    {
        $this->seedPermissions();

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $target = User::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($superAdmin)->put(route('identity.users.update', $target), [
            'name' => 'Nama Baru',
            'email' => $target->email,
            'roles' => [],
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => User::class,
            'auditable_id' => $target->id,
            'action' => 'update',
        ]);

        $log = AuditLog::where('auditable_id', $target->id)->where('action', 'update')->latest()->first();

        $this->assertEquals('Nama Baru', $log->new_values['name']);
    }
}
