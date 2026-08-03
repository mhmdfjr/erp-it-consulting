<?php

namespace Tests\Unit\Modules\Finance\Services;

use App\Modules\Finance\Exceptions\NonPostableAccountException;
use App\Modules\Finance\Exceptions\UnbalancedJournalEntryException;
use App\Modules\Finance\Exceptions\VoidReasonRequiredException;
use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Services\JournalEntryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalEntryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected JournalEntryService $service;
    protected ChartOfAccount $kas;
    protected ChartOfAccount $pendapatan;
    protected ChartOfAccount $headerAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(JournalEntryService::class);

        $this->headerAccount = ChartOfAccount::create([
            'code' => '100', 'name' => 'Aktiva Lancar', 'account_type' => 'asset',
            'is_postable' => false, 'is_active' => true,
        ]);

        $this->kas = ChartOfAccount::create([
            'code' => '101', 'name' => 'Kas', 'account_type' => 'asset',
            'parent_id' => $this->headerAccount->id, 'is_postable' => true, 'is_active' => true,
        ]);

        $this->pendapatan = ChartOfAccount::create([
            'code' => '401', 'name' => 'Pendapatan Jasa', 'account_type' => 'revenue',
            'is_postable' => true, 'is_active' => true,
        ]);
    }

    public function test_entry_balance_berhasil_dibuat(): void
    {
        $entry = $this->service->createEntry([
            'entry_date' => '2026-08-01',
            'description' => 'Test balanced entry',
            'lines' => [
                ['account_id' => $this->kas->id, 'debit' => 1000000, 'credit' => 0],
                ['account_id' => $this->pendapatan->id, 'debit' => 0, 'credit' => 1000000],
            ],
        ]);

        $this->assertEquals('posted', $entry->status);
        $this->assertMatchesRegularExpression('/^JE-2026-\d{6}$/', $entry->entry_number);
        $this->assertCount(2, $entry->lines);
    }

    public function test_entry_tidak_balance_ditolak(): void
    {
        $this->expectException(UnbalancedJournalEntryException::class);

        $this->service->createEntry([
            'entry_date' => '2026-08-01',
            'lines' => [
                ['account_id' => $this->kas->id, 'debit' => 1000000, 'credit' => 0],
                ['account_id' => $this->pendapatan->id, 'debit' => 0, 'credit' => 900000],
            ],
        ]);
    }

    public function test_posting_ke_akun_non_postable_ditolak(): void
    {
        $this->expectException(NonPostableAccountException::class);

        $this->service->createEntry([
            'entry_date' => '2026-08-01',
            'lines' => [
                ['account_id' => $this->headerAccount->id, 'debit' => 1000000, 'credit' => 0],
                ['account_id' => $this->pendapatan->id, 'debit' => 0, 'credit' => 1000000],
            ],
        ]);
    }

    public function test_entry_number_increment_dalam_tahun_yang_sama(): void
    {
        $first = $this->service->createEntry([
            'entry_date' => '2026-08-01',
            'lines' => [
                ['account_id' => $this->kas->id, 'debit' => 100, 'credit' => 0],
                ['account_id' => $this->pendapatan->id, 'debit' => 0, 'credit' => 100],
            ],
        ]);

        $second = $this->service->createEntry([
            'entry_date' => '2026-08-15',
            'lines' => [
                ['account_id' => $this->kas->id, 'debit' => 200, 'credit' => 0],
                ['account_id' => $this->pendapatan->id, 'debit' => 0, 'credit' => 200],
            ],
        ]);

        $this->assertEquals('JE-2026-000001', $first->entry_number);
        $this->assertEquals('JE-2026-000002', $second->entry_number);
    }

    public function test_void_mengubah_status_tanpa_mengubah_nilai_lines(): void
    {
        $entry = $this->service->createEntry([
            'entry_date' => '2026-08-01',
            'lines' => [
                ['account_id' => $this->kas->id, 'debit' => 500000, 'credit' => 0],
                ['account_id' => $this->pendapatan->id, 'debit' => 0, 'credit' => 500000],
            ],
        ]);

        $voided = $this->service->voidEntry($entry, 'Salah input nominal');

        $this->assertEquals('void', $voided->status);
        $this->assertEquals('Salah input nominal', $voided->void_reason);
        $this->assertEquals(500000, $voided->lines->firstWhere('account_id', $this->kas->id)->debit);
        $this->assertEquals(500000, $voided->lines->firstWhere('account_id', $this->pendapatan->id)->credit);
    }

    public function test_void_tanpa_reason_ditolak(): void
    {
        $entry = $this->service->createEntry([
            'entry_date' => '2026-08-01',
            'lines' => [
                ['account_id' => $this->kas->id, 'debit' => 100, 'credit' => 0],
                ['account_id' => $this->pendapatan->id, 'debit' => 0, 'credit' => 100],
            ],
        ]);

        $this->expectException(VoidReasonRequiredException::class);

        $this->service->voidEntry($entry, '');
    }
}
