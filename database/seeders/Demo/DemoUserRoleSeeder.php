<?php
// database/seeders/Demo/DemoUserRoleSeeder.php

namespace Database\Seeders\Demo;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeder DEMO, bukan production. Membuat role tambahan (subset permission dari
 * RolePermissionSeeder) plus user contoh per role, untuk simulasi dan bahan
 * negative-test authorization di M4 task 4.7.
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
     * JANGAN dipakai di production.
     */
    private const DEMO_PASSWORD = 'password';

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

        // User contoh per role, 2 user untuk role staff (variasi), 1 untuk role manager.
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
