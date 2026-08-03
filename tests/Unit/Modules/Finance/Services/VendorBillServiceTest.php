<?php

namespace Tests\Unit\Modules\Finance\Services;

use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Models\Vendor;
use App\Modules\Finance\Services\VendorBillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorBillServiceTest extends TestCase
{
    use RefreshDatabase;

    protected VendorBillService $service;
    protected ChartOfAccount $utangUsaha;
    protected ChartOfAccount $bebanKantor;
    protected ChartOfAccount $kas;
    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(VendorBillService::class);

        $this->utangUsaha = ChartOfAccount::create([
            'code' => '201', 'name' => 'Utang Usaha', 'account_type' => 'liability',
            'is_postable' => true, 'is_active' => true,
        ]);

        $this->bebanKantor = ChartOfAccount::create([
            'code' => '516', 'name' => 'Beban Perlengkapan Kantor', 'account_type' => 'expense',
            'is_postable' => true, 'is_active' => true,
        ]);

        $this->kas = ChartOfAccount::create([
            'code' => '101', 'name' => 'Kas', 'account_type' => 'asset',
            'is_postable' => true, 'is_active' => true,
        ]);

        $this->vendor = Vendor::create(['name' => 'PT Supplier Kantor']);
    }

    public function test_create_bill_generate_journal_entry_accrual_yang_benar(): void
    {
        $bill = $this->service->createBill([
            'vendor_id' => $this->vendor->id,
            'account_id' => $this->bebanKantor->id,
            'bill_number' => 'INV-001',
            'bill_date' => '2026-08-01',
            'due_date' => '2026-08-31',
            'amount' => 750000,
        ]);

        $this->assertEquals('unpaid', $bill->status);

        $entry = $bill->fresh()->wasRecentlyCreated ? null : null; // noop, ambil entry via query
        $entry = \App\Modules\Finance\Models\JournalEntry::where('reference_type', \App\Modules\Finance\Models\VendorBill::class)
            ->where('reference_id', $bill->id)
            ->with('lines')
            ->firstOrFail();

        $this->assertEquals('posted', $entry->status);
        $this->assertEquals(750000, $entry->lines->firstWhere('account_id', $this->bebanKantor->id)->debit);
        $this->assertEquals(750000, $entry->lines->firstWhere('account_id', $this->utangUsaha->id)->credit);
    }

    public function test_mark_as_paid_generate_journal_entry_pelunasan_yang_benar(): void
    {
        $bill = $this->service->createBill([
            'vendor_id' => $this->vendor->id,
            'account_id' => $this->bebanKantor->id,
            'bill_number' => 'INV-002',
            'bill_date' => '2026-08-01',
            'due_date' => '2026-08-31',
            'amount' => 500000,
        ]);

        $paid = $this->service->markAsPaid($bill, $this->kas->id);

        $this->assertEquals('paid', $paid->status);

        $paymentEntry = \App\Modules\Finance\Models\JournalEntry::where('reference_type', \App\Modules\Finance\Models\VendorBill::class)
            ->where('reference_id', $bill->id)
            ->where('description', "Pelunasan Vendor Bill {$bill->bill_number}")
            ->with('lines')
            ->firstOrFail();

        $this->assertEquals(500000, $paymentEntry->lines->firstWhere('account_id', $this->utangUsaha->id)->debit);
        $this->assertEquals(500000, $paymentEntry->lines->firstWhere('account_id', $this->kas->id)->credit);
    }
}
