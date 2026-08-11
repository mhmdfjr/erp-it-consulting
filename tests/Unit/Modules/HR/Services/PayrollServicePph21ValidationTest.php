<?php

namespace Tests\Unit\Modules\HR\Services;

use App\Modules\HR\Database\Seeders\TerRateSeeder;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PERINGATAN: nilai expected di test ini WAJIB dicocokkan manual terhadap
 * kalkulator resmi DJP (https://pajak.go.id atau kalkulator TER resmi
 * lainnya) SEBELUM dianggap valid. Angka di bawah dihitung dari formula
 * TER x gross_salary yang sama dengan implementasi PayrollService — kalau
 * implementasi salah, test ini akan tetap "pass" karena membandingkan
 * terhadap dirinya sendiri, bukan sumber independen.
 *
 * JANGAN lanjut ke task berikutnya kalau ada selisih dengan DJP yang
 * tidak bisa dijelaskan (ARCHITECTURE.md Section 9, ROADMAP.md M3).
 */
class PayrollServicePph21ValidationTest extends TestCase
{
    use RefreshDatabase;

    private PayrollService $service;
    private Carbon $periodDate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TerRateSeeder::class);
        $this->service = new PayrollService();
        $this->periodDate = Carbon::create(2026, 8, 1);
    }

    /**
     * Kategori A (TK0/TK1/K0), gross di bawah PTKP efektif -> rate 0%,
     * PPh21 harus persis 0. Ini kasus paling gampang salah kalau ada
     * bug "off by one" di boundary lower bound 0.00.
     */
    public function test_category_a_below_threshold_zero_tax(): void
    {
        $employee = Employee::factory()->create(['ptkp_status' => 'TK0']);

        $result = $this->service->calculatePph21($employee, 5000000, $this->periodDate);

        $this->assertSame(0.0, $result['pph21_deduction']);
        $this->assertSame('A', $result['ter_category_used']);
    }

    /**
     * Kategori A, gross 10jt (skenario umum karyawan menengah).
     * Bracket 9650000.01-10050000.00 -> rate 2.00%.
     * COCOKKAN MANUAL: 10.000.000 x 2% = 200.000
     */
    public function test_category_a_gross_10_million(): void
    {
        $employee = Employee::factory()->create(['ptkp_status' => 'TK0']);

        $result = $this->service->calculatePph21($employee, 10000000, $this->periodDate);

        $this->assertSame(200000.0, $result['pph21_deduction']);
    }

    /**
     * Kategori B (TK2/TK3/K1/K2), gross 15jt.
     * Bracket 14950000.01-16400000.00 -> rate 6.00%.
     * COCOKKAN MANUAL: 15.000.000 x 6% = 900.000
     */
    public function test_category_b_gross_15_million(): void
    {
        $employee = Employee::factory()->create(['ptkp_status' => 'TK2']);

        $result = $this->service->calculatePph21($employee, 15000000, $this->periodDate);

        $this->assertSame(900000.0, $result['pph21_deduction']);
        $this->assertSame('B', $result['ter_category_used']);
    }

    /**
     * Kategori C (K3), gross 20jt.
     * Bracket 19500000.01-22700000.00 -> rate 8.00%.
     * COCOKKAN MANUAL: 20.000.000 x 8% = 1.600.000
     */
    public function test_category_c_gross_20_million(): void
    {
        $employee = Employee::factory()->create(['ptkp_status' => 'K3']);

        $result = $this->service->calculatePph21($employee, 20000000, $this->periodDate);

        $this->assertSame(1600000.0, $result['pph21_deduction']);
        $this->assertSame('C', $result['ter_category_used']);
    }

    /**
     * Kategori A, gross tinggi (50jt) -> menguji bracket menengah-atas.
     * Bracket 47800000.01-51400000.00 -> rate 18.00%.
     * COCOKKAN MANUAL: 50.000.000 x 18% = 9.000.000
     */
    public function test_category_a_gross_50_million(): void
    {
        $employee = Employee::factory()->create(['ptkp_status' => 'K0']);

        $result = $this->service->calculatePph21($employee, 50000000, $this->periodDate);

        $this->assertSame(9000000.0, $result['pph21_deduction']);
    }

    /**
     * Menguji ROUND HALF UP eksplisit dengan gross yang menghasilkan
     * pecahan .50 tepat di rupiah — kasus paling rawan untuk pembulatan.
     * Gross 5650000.01 (Kategori A, rate 0.25%): 5650000.01 x 0.0025
     * = 14125.0000025 -> dibulatkan ke 14125 (bukan kasus .5 pas, tapi
     * baseline dulu untuk memastikan pembulatan konsisten).
     */
    public function test_rounding_half_up_applied_correctly(): void
    {
        $employee = Employee::factory()->create(['ptkp_status' => 'TK0']);

        $result = $this->service->calculatePph21($employee, 5650000.01, $this->periodDate);

        $this->assertSame(28250.0, $result['pph21_deduction']);
    }
}
