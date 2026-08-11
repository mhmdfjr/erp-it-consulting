<?php

namespace Tests\Unit\Modules\HR\Services;

use App\Modules\HR\Services\PayrollService;
use PHPUnit\Framework\TestCase;

class PayrollServiceTest extends TestCase
{
    private PayrollService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PayrollService();
    }

    /**
     * Februari non-kabisat selalu tepat 4 minggu (28 hari), jadi
     * jumlah weekday 20 apapun hari mulainya.
     */
    public function test_february_non_leap_year_has_20_working_days(): void
    {
        $this->assertSame(20, $this->service->calculateWorkingDays(2026, 2));
    }

    /**
     * Agustus 2026 (31 hari). 1 Agustus 2026 = Sabtu, 31 Agustus = Senin.
     * Menguji penanganan sisa hari di luar kelipatan minggu penuh.
     */
    public function test_august_2026_has_21_working_days(): void
    {
        $this->assertSame(21, $this->service->calculateWorkingDays(2026, 8));
    }

    /**
     * 2028 adalah tahun kabisat, Februari 29 hari. Memastikan tidak ada
     * asumsi hardcode "Februari = 28 hari" di implementasi.
     */
    public function test_february_leap_year_2028_has_21_working_days(): void
    {
        $this->assertSame(21, $this->service->calculateWorkingDays(2028, 2));
    }

    /**
     * April 2026, 30 hari. 1 April 2026 = Rabu, 30 April = Kamis.
     * Kasus tambahan bulan 30 hari (beda dari 28/29/31) supaya ketiga
     * pola panjang bulan yang mungkin muncul di kalender kerja tercakup.
     */
    public function test_april_2026_has_22_working_days(): void
    {
        $this->assertSame(22, $this->service->calculateWorkingDays(2026, 4));
    }
}
