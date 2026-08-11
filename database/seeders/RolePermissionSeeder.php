<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Daftar permission dasar per module.
     */
    protected array $permissions = [
        'identity.manage',
        'identity.user.view',
        'identity.user.create',
        'identity.user.update',
        'identity.user.delete',
        'identity.role.view',
        'identity.role.create',
        'identity.role.update',
        'identity.role.delete',
        'identity.settings.manage',

        'finance.manage',
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

        'sales.manage',
        'sales.item.view',
        'sales.item.create',
        'sales.item.update',
        'sales.customer.view',
        'sales.customer.create',
        'sales.customer.update',
        'sales.order.view',
        'sales.order.create',
        'sales.order.complete',
        'sales.order.cancel',
        'sales.inventory.view',
        'sales.inventory.adjust',
        'sales.category.create',

        // HR & Payroll - placeholder, di-refine saat M3.
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
        'hr.payroll.pay'
    ];

    public function run(): void
    {
        Cache::forget(config('permission.cache.key'));

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());
    }
}
