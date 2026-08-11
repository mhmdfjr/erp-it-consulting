<?php

namespace Tests\Unit\Modules\HR\Services;

use App\Modules\HR\Database\Seeders\BpjsRateSeeder;
use App\Modules\HR\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollServiceBpjsTest extends TestCase
{
    use RefreshDatabase;

    private PayrollService $service;
    private Carbon $periodDate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BpjsRateSeeder::class);
        $this->service = new PayrollService();
        $this->periodDate = Carbon::create(2026, 8, 1);
    }

    /**
     * Gross di bawah max_wage_base Kesehatan (12jt) -> basis pakai gross
     * aktual, bukan cap. Angka ini sudah diverifikasi manual lewat tinker
     * sebelumnya di task 3.16.
     */
    public function test_below_max_wage_base_uses_actual_gross(): void
    {
        $result = $this->service->calculateBpjsDeductions(10000000, $this->periodDate);

        $this->assertSame(100000.0, $result['bpjs_kesehatan_deduction']); // 10jt x 1%
        $this->assertSame(200000.0, $result['bpjs_jht_deduction']);       // 10jt x 2%
        $this->assertSame(100000.0, $result['bpjs_jp_deduction']);       // 10jt x 1%
        $this->assertSame(400000.0, $result['total_bpjs_deduction']);
    }

    /**
     * KRITIS: gross di atas max_wage_base Kesehatan (12jt) -> Kesehatan
     * HARUS pakai cap 12jt sebagai basis, BUKAN gross aktual. JHT/JP
     * TIDAK ada cap (max_wage_base null di seed data), harus tetap
     * proporsional terhadap gross aktual.
     */
    public function test_above_max_wage_base_caps_kesehatan_only(): void
    {
        $result = $this->service->calculateBpjsDeductions(20000000, $this->periodDate);

        // Kesehatan kena cap: 12jt (BUKAN 20jt) x 1% = 120.000
        $this->assertSame(120000.0, $result['bpjs_kesehatan_deduction']);

        // JHT/JP TIDAK ada cap, tetap dari gross aktual 20jt
        $this->assertSame(400000.0, $result['bpjs_jht_deduction']); // 20jt x 2%
        $this->assertSame(200000.0, $result['bpjs_jp_deduction']);  // 20jt x 1%

        $this->assertSame(720000.0, $result['total_bpjs_deduction']);
    }

    /**
     * Gross TEPAT di max_wage_base (12jt persis) -> boundary case, harus
     * pakai 12jt baik dari jalur "basis = cap" maupun "basis = gross",
     * hasilnya sama karena kebetulan sama persis. Menguji operator
     * perbandingan (min()) tidak salah di titik pas sama.
     */
    public function test_gross_exactly_at_max_wage_base(): void
    {
        $result = $this->service->calculateBpjsDeductions(12000000, $this->periodDate);

        $this->assertSame(120000.0, $result['bpjs_kesehatan_deduction']); // 12jt x 1%
    }

    /**
     * Gross SATU RUPIAH di atas max_wage_base -> harus tetap kena cap
     * penuh (12jt), bukan 12000001 x 1%. Menguji boundary transition
     * sangat dekat, mirip pola test boundary bracket TER sebelumnya.
     */
    public function test_gross_one_rupiah_above_max_wage_base_still_capped(): void
    {
        $result = $this->service->calculateBpjsDeductions(12000001, $this->periodDate);

        $this->assertSame(120000.0, $result['bpjs_kesehatan_deduction']); // tetap 12jt x 1%, bukan 12000001 x 1%
    }

    /**
     * Gross sangat rendah (di bawah semua threshold wajar) -> memastikan
     * tidak ada division by zero atau error lain di angka kecil.
     */
    public function test_low_gross_no_errors(): void
    {
        $result = $this->service->calculateBpjsDeductions(1000000, $this->periodDate);

        $this->assertSame(10000.0, $result['bpjs_kesehatan_deduction']); // 1jt x 1%
        $this->assertSame(20000.0, $result['bpjs_jht_deduction']);       // 1jt x 2%
        $this->assertSame(10000.0, $result['bpjs_jp_deduction']);       // 1jt x 1%
    }

    /**
     * Melempar exception jelas kalau rate tidak ditemukan untuk periode
     * tertentu (misal effective_date di masa depan yang belum berlaku).
     * Ini bukan soal angka, tapi soal "gagal keras, bukan gagal senyap"
     * sesuai alasan di task 3.16.
     */
    public function test_throws_when_no_rate_found_for_period(): void
    {
        $this->expectException(\RuntimeException::class);

        // Tahun jauh sebelum effective_date manapun di seed data (2026-01-01)
        $this->service->calculateBpjsDeductions(10000000, Carbon::create(2020, 1, 1));
    }
}
