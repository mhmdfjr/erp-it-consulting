<?php

namespace App\Modules\HR\Database\Seeders;

use App\Modules\HR\Models\PayrollComponent;
use Illuminate\Database\Seeder;

class PayrollComponentSeeder extends Seeder
{
    /**
     * Belum ada komponen gaji standar perusahaan yang dikonfirmasi saat
     * seeder ini ditulis (TASKS.md task 3.12 mengizinkan dikosongkan).
     * Isi array $components di bawah kalau nanti sudah ada daftar
     * tunjangan/potongan tetap yang berlaku umum, atau tambahkan manual
     * lewat UI PayrollComponentController (task 3.23) per kebutuhan.
     */
    public function run(): void
    {
        $components = [
            [
                'name' => 'Tunjangan Transport',
                'type' => 'earning',
                'calculation_type' => 'fixed_amount',
                'is_active' => true,
            ],
        ];

        foreach ($components as $component) {
            PayrollComponent::updateOrCreate(
                ['name' => $component['name']],
                $component
            );
        }
    }
}
