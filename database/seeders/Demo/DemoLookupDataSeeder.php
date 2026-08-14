<?php
// database/seeders/Demo/DemoLookupDataSeeder.php

namespace Database\Seeders\Demo;

use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Position;
use App\Modules\SalesInventory\Models\ItemCategory;
use Illuminate\Database\Seeder;

/**
 * Seeder DEMO untuk lookup/reference data sederhana: Department, Position,
 * ItemCategory. Bukan production seed — data ini contoh struktur organisasi
 * dan kategori barang untuk simulasi, bukan struktur riil perusahaan.
 *
 * Idempotent lewat firstOrCreate berdasarkan field unik (name/title),
 * aman dijalankan ulang.
 */
class DemoLookupDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            abort(403, 'DemoLookupDataSeeder tidak boleh dijalankan di production.');
        }

        $departmentPositions = [
            'Engineering' => ['Software Engineer', 'IT Consultant', 'Technical Lead'],
            'Sales & Marketing' => ['Sales Executive', 'Account Manager'],
            'Finance & Accounting' => ['Staff Finance', 'Finance Manager'],
            'Human Resources' => ['Staff HR', 'HR Manager'],
            'Operations' => ['Warehouse Staff', 'Procurement Officer'],
        ];

        foreach ($departmentPositions as $departmentName => $positionTitles) {
            $department = Department::firstOrCreate(['name' => $departmentName]);

            foreach ($positionTitles as $title) {
                Position::firstOrCreate([
                    'department_id' => $department->id,
                    'title' => $title,
                ]);
            }
        }

        $itemCategories = [
            'Hardware Komputer',
            'Peralatan Jaringan',
            'Lisensi Software',
            'Peralatan Kantor',
            'Jasa Konsultasi',
            'Jasa Implementasi & Support',
        ];

        foreach ($itemCategories as $categoryName) {
            ItemCategory::firstOrCreate(['name' => $categoryName]);
        }

        $this->command->info('Demo lookup data (Department, Position, ItemCategory) selesai.');
    }
}
