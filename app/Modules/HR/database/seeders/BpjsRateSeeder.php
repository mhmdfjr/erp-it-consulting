<?php

namespace App\Modules\HR\Database\Seeders;

use App\Modules\HR\Models\BpjsRate;
use Illuminate\Database\Seeder;

class BpjsRateSeeder extends Seeder
{
    /**
     * Sumber: DATABASE.md Appendix B, effective_date = 2026-01-01.
     *
     * PERINGATAN — DUA ANGKA BELUM DIKONFIRMASI RESMI (lihat TASKS.md
     * "Item yang Masih Perlu Diverifikasi", VERIFIKASI 2):
     * 1. jkk.rate_company_percentage = 0.24 adalah ASUMSI kelas risiko
     *    terendah, bukan kelas risiko resmi terdaftar perusahaan ini.
     * 2. jp.max_wage_base = null (tanpa batas) adalah ASUMSI karena sumber
     *    data tidak menyebutkan wage cap eksplisit untuk JP tahun 2026.
     *
     * JANGAN anggap dua angka ini final sebelum konfirmasi resmi ke BPJS
     * Ketenagakerjaan. Cek DATABASE.md Appendix B untuk detail lengkap.
     */
    public function run(): void
    {
        $effectiveDate = '2026-01-01';

        $rates = [
            [
                'bpjs_type' => 'kesehatan',
                'rate_employee_percentage' => 1.00,
                'rate_company_percentage' => 4.00,
                'max_wage_base' => 12000000.00,
            ],
            [
                'bpjs_type' => 'jht',
                'rate_employee_percentage' => 2.00,
                'rate_company_percentage' => 3.70,
                'max_wage_base' => null,
            ],
            [
                'bpjs_type' => 'jp',
                'rate_employee_percentage' => 1.00,
                'rate_company_percentage' => 2.00,
                // BELUM DIKONFIRMASI — lihat VERIFIKASI 2 di TASKS.md
                'max_wage_base' => null,
            ],
            [
                'bpjs_type' => 'jkm',
                'rate_employee_percentage' => 0.00,
                'rate_company_percentage' => 0.30,
                'max_wage_base' => null,
            ],
            [
                'bpjs_type' => 'jkk',
                'rate_employee_percentage' => 0.00,
                // ASUMSI kelas risiko terendah — lihat VERIFIKASI 2 di TASKS.md
                'rate_company_percentage' => 0.24,
                'max_wage_base' => null,
            ],
        ];

        foreach ($rates as $rate) {
            BpjsRate::updateOrCreate(
                [
                    'bpjs_type' => $rate['bpjs_type'],
                    'effective_date' => $effectiveDate,
                ],
                [
                    'rate_employee_percentage' => $rate['rate_employee_percentage'],
                    'rate_company_percentage' => $rate['rate_company_percentage'],
                    'max_wage_base' => $rate['max_wage_base'],
                    'end_date' => null,
                ]
            );
        }
    }
}
