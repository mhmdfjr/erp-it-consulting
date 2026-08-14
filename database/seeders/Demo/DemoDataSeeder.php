<?php
namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            abort(403, 'DemoDataSeeder tidak boleh dijalankan di production.');
        }

        $this->call([
            DemoUserRoleSeeder::class,
            DemoLookupDataSeeder::class,
            DemoVendorItemCustomerSeeder::class,
            DemoEmployeeAttendanceSeeder::class,
            DemoSalesOrderSeeder::class,
            DemoPayrollSeeder::class,
            DemoPaymentSeeder::class
        ]);
    }
}
