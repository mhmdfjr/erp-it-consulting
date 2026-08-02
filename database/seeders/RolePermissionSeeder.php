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
     *
     * Granularity sengaja kasar untuk module yang belum dibangun (Finance, SalesInventory, HR),
     * cuma permission 'manage' level tinggi. Akan di-refine jadi lebih granular
     * (create/view/update/delete per resource) begitu module tersebut benar-benar dibangun.
     */
    protected array $permissions = [
        // Identity - sudah dibangun di M0, granularity lebih detail.
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

        // Finance - placeholder, di-refine saat M1.
        'finance.manage',

        // Sales & Inventory - placeholder, di-refine saat M2.
        'sales.manage',

        // HR & Payroll - placeholder, di-refine saat M3.
        'hr.manage',
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
