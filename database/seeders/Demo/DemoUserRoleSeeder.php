<?php
// database/seeders/Demo/DemoUserRoleSeeder.php

namespace Database\Seeders\Demo;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeder DEMO, bukan production. Membuat role tambahan plus user contoh per role, untuk simulasi dan bahan
 * negative-test authorization
 *
 * Prasyarat: RolePermissionSeeder sudah dijalankan lebih dulu (permission harus
 * sudah ada di database sebelum di-sync ke role baru di sini).
 *
 * Jalankan manual: php artisan db:seed --class="Database\Seeders\Demo\DemoUserRoleSeeder"
 */
class DemoUserRoleSeeder extends Seeder
{
    /**
     * Password default seluruh user demo. Sengaja sama untuk kemudahan testing manual,
     * JANGAN dipakai di production. Super Admin TIDAK memakai konstanta ini -- password-nya
     * datang dari config('app.super_admin_password') yang di-set eksplisit lewat env
     * Railway, terpisah dari seluruh user demo di sini.
     */
    private const DEMO_PASSWORD = 'password';

    /**
     * Daftar permission PENUH per domain, dipisah dari $roleDefinitions supaya role
     * staff/manager (subset) dan role Admin (union penuh) sama-sama bersumber dari
     * satu daftar kebenaran. Kalau ada permission baru ditambahkan ke
     * RolePermissionSeeder, cukup update array ini di satu tempat.
     */
    private const FINANCE_PERMISSIONS_FULL = [
        'finance.coa.view',
        'finance.journal.view', 'finance.journal.create', 'finance.journal.void',
        'finance.report.view',
        'finance.vendor.view', 'finance.vendor.manage',
        'finance.vendorbill.view', 'finance.vendorbill.create', 'finance.vendorbill.pay',
        'finance.invoice.view', 'finance.invoice.pay',
    ];

    private const SALES_PERMISSIONS_FULL = [
        'sales.item.view', 'sales.item.create', 'sales.item.update',
        'sales.category.manage',
        'sales.customer.view', 'sales.customer.create', 'sales.customer.update',
        'sales.order.view', 'sales.order.create', 'sales.order.complete', 'sales.order.cancel',
        'sales.inventory.view', 'sales.inventory.adjust',
    ];

    private const HR_PERMISSIONS_FULL = [
        'hr.department.manage', 'hr.position.manage',
        'hr.employee.view', 'hr.employee.create', 'hr.employee.update',
        'hr.attendance.view', 'hr.attendance.manage',
        'hr.payrollcomponent.view', 'hr.payrollcomponent.manage',
        'hr.payroll.view', 'hr.payroll.process', 'hr.payroll.pay',
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            abort(403, 'DemoUserRoleSeeder tidak boleh dijalankan di production.');
        }

        $roleDefinitions = [
            'Sales Staff' => [
                'sales.item.view', 'sales.item.create', 'sales.item.update',
                'sales.customer.view', 'sales.customer.create', 'sales.customer.update',
                'sales.order.view', 'sales.order.create', 'sales.order.complete', 'sales.order.cancel',
                'sales.inventory.view',
                'finance.invoice.view',
            ],
            'Finance Staff' => [
                'finance.coa.view',
                'finance.journal.view', 'finance.journal.create', 'finance.report.view',
                'finance.vendor.view', 'finance.vendor.manage',
                'finance.vendorbill.view', 'finance.vendorbill.create', 'finance.vendorbill.pay',
                'finance.invoice.view', 'finance.invoice.pay',
            ],
            'HR Staff' => [
                'hr.employee.view', 'hr.employee.create', 'hr.employee.update',
                'hr.attendance.view', 'hr.attendance.manage',
                'hr.payrollcomponent.view',
            ],
        ];

        // Finance Manager = Finance Staff + void journal
        $roleDefinitions['Finance Manager'] = array_merge(
            $roleDefinitions['Finance Staff'],
            ['finance.journal.void']
        );

        // HR Manager = HR Staff + payroll processing + config
        $roleDefinitions['HR Manager'] = array_merge(
            $roleDefinitions['HR Staff'],
            [
                'hr.payroll.view', 'hr.payroll.process', 'hr.payroll.pay',
                'hr.payrollcomponent.manage',
                'hr.department.manage', 'hr.position.manage',
            ]
        );

        /**
         * Admin = union PENUH permission HR + Finance + Sales, TIDAK termasuk
         * identity.* apapun (user.*, role.*, settings.manage). Ini satu-satunya
         * pembeda dari Super Admin -- Admin bisa operasikan seluruh module bisnis,
         * tapi tidak bisa ubah user/role/permission/company profile/system setting.
         */
        $roleDefinitions['Admin'] = array_merge(
            self::FINANCE_PERMISSIONS_FULL,
            self::SALES_PERMISSIONS_FULL,
            self::HR_PERMISSIONS_FULL,
        );

        foreach ($roleDefinitions as $roleName => $permissionNames) {
            $existingPermissions = Permission::whereIn('name', $permissionNames)->get();

            if ($existingPermissions->count() !== count($permissionNames)) {
                $missing = array_diff($permissionNames, $existingPermissions->pluck('name')->all());
                $this->command->warn(
                    "Role '{$roleName}': permission berikut belum ada di database, dilewati: "
                    . implode(', ', $missing)
                    . ". Pastikan RolePermissionSeeder sudah dijalankan lebih dulu."
                );
            }

            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($existingPermissions);
        }

        // User contoh per role, 2 user untuk role staff (variasi), 1 untuk role manager/admin.
        $userSeeds = [
            ['name' => 'Dewi Sales', 'email' => 'dewi.sales@test.local', 'role' => 'Sales Staff'],
            ['name' => 'Rian Sales', 'email' => 'rian.sales@test.local', 'role' => 'Sales Staff'],
            ['name' => 'Fajar Finance', 'email' => 'fajar.finance@test.local', 'role' => 'Finance Staff'],
            ['name' => 'Nina Finance', 'email' => 'nina.finance@test.local', 'role' => 'Finance Staff'],
            ['name' => 'Budi Finance Manager', 'email' => 'budi.financemanager@test.local', 'role' => 'Finance Manager'],
            ['name' => 'Sari HR', 'email' => 'sari.hr@test.local', 'role' => 'HR Staff'],
            ['name' => 'Agus HR', 'email' => 'agus.hr@test.local', 'role' => 'HR Staff'],
            ['name' => 'Lina HR Manager', 'email' => 'lina.hrmanager@test.local', 'role' => 'HR Manager'],
            ['name' => 'Eko Resigned', 'email' => 'eko.resigned@test.local', 'role' => 'HR Staff', 'is_active' => false],
            // Admin: akses penuh HR+Finance+Sales, tidak bisa sentuh Identity.
            ['name' => 'Andi Admin', 'email' => 'andi.admin@test.local', 'role' => 'Admin'],
        ];

        foreach ($userSeeds as $seed) {
            $user = User::firstOrCreate(
                ['email' => $seed['email']],
                [
                    'name' => $seed['name'],
                    'password' => Hash::make(self::DEMO_PASSWORD),
                    'is_active' => $seed['is_active'] ?? true,
                ]
            );

            if (! $user->hasRole($seed['role'])) {
                $user->syncRoles([$seed['role']]);
            }
        }

        $this->command->info('Demo user & role selesai. Password seluruh user demo: ' . self::DEMO_PASSWORD);
    }
}
