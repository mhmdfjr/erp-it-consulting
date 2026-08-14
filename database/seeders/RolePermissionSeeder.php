<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Daftar permission dasar per module. Granular saja, tidak ada lagi placeholder
     * kasar ({module}.manage) — seluruh module M0-M3 sudah di-refine ke permission
     * spesifik per resource, konsisten dengan SESSION_SUMMARY_M1/M2/M3.
     */
    protected array $permissions = [
        // Identity
        'identity.user.view',
        'identity.user.create',
        'identity.user.update',
        'identity.user.delete',
        'identity.role.view',
        'identity.role.create',
        'identity.role.update',
        'identity.role.delete',
        'identity.settings.manage',

        // Finance
        'finance.coa.view',
        'finance.journal.view',
        'finance.journal.create',
        'finance.journal.void',
        'finance.vendor.view',
        'finance.vendor.manage',
        'finance.vendorbill.view',
        'finance.vendorbill.create',
        'finance.vendorbill.pay',
        'finance.invoice.view',
        'finance.invoice.pay',

        // Sales & Inventory
        'sales.item.view',
        'sales.item.create',
        'sales.item.update',
        'sales.category.manage',
        'sales.customer.view',
        'sales.customer.create',
        'sales.customer.update',
        'sales.order.view',
        'sales.order.create',
        'sales.order.complete',
        'sales.order.cancel',
        'sales.inventory.view',
        'sales.inventory.adjust',

        // HR & Payroll
        'hr.department.manage',
        'hr.position.manage',
        'hr.employee.view',
        'hr.employee.create',
        'hr.employee.update',
        'hr.attendance.view',
        'hr.attendance.manage',
        'hr.payrollcomponent.view',
        'hr.payrollcomponent.manage',
        'hr.payroll.view',
        'hr.payroll.process',
        'hr.payroll.pay',
    ];

    public function run(): void
    {
        Cache::forget(config('permission.cache.key'));

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $this->seedSuperAdminUser();
    }

    /**
     * Bootstrap account wajib di setiap environment (dev, staging, production).
     * Idempotent: kalau user sudah ada, password TIDAK ditimpa (misal sudah diganti
     * manual oleh admin setelah login pertama), cuma role yang disinkronkan ulang.
     */
    private function seedSuperAdminUser(): void
    {
        $email = config('app.super_admin_email');
        $password = config('app.super_admin_password');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'is_active' => true,
            ]
        );

        if (! $user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
        }

        if ($user->wasRecentlyCreated) {
            $this->command->warn(
                "Super Admin dibuat: {$email} / password dari config('app.super_admin_password')."
                . ' Ganti password ini segera setelah login pertama di environment non-dev.'
            );
        }
    }
}
