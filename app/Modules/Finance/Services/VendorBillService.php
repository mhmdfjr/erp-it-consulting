<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Models\VendorBill;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VendorBillService
{
    public function __construct(
        protected JournalEntryService $journalEntryService
    ) {
    }

    /**
     * Buat vendor bill + jurnal accrual otomatis (debit account_id dari bill, kredit 201 Utang Usaha).
     *
     * $data: vendor_id, account_id, bill_number, bill_date, due_date, amount, created_by (nullable)
     */
    public function createBill(array $data): VendorBill
    {
        return DB::transaction(function () use ($data) {
            $bill = VendorBill::create([
                'vendor_id' => $data['vendor_id'],
                'account_id' => $data['account_id'],
                'bill_number' => $data['bill_number'],
                'bill_date' => $data['bill_date'],
                'due_date' => $data['due_date'],
                'amount' => $data['amount'],
                'status' => 'unpaid',
            ]);

            $payableAccount = $this->utangUsahaAccount();

            $this->journalEntryService->createEntry([
                'entry_date' => $bill->bill_date,
                'reference_type' => VendorBill::class,
                'reference_id' => $bill->id,
                'description' => "Vendor Bill {$bill->bill_number}",
                'created_by' => $data['created_by'] ?? null,
                'lines' => [
                    ['account_id' => $bill->account_id, 'debit' => $bill->amount, 'credit' => 0],
                    ['account_id' => $payableAccount->id, 'debit' => 0, 'credit' => $bill->amount],
                ],
            ]);

            return $bill->fresh();
        });
    }

    /**
     * Tandai bill lunas + jurnal pelunasan (debit 201 Utang Usaha, kredit akun kas/bank yang dipilih).
     */
    public function markAsPaid(VendorBill $bill, int $paymentAccountId, ?int $createdBy = null): VendorBill
    {
        if ($bill->status !== 'unpaid') {
            throw new RuntimeException("Vendor bill {$bill->bill_number} tidak berstatus unpaid, tidak bisa ditandai lunas.");
        }

        return DB::transaction(function () use ($bill, $paymentAccountId, $createdBy) {
            $bill->update(['status' => 'paid']);

            $payableAccount = $this->utangUsahaAccount();

            $this->journalEntryService->createEntry([
                'entry_date' => now()->toDateString(),
                'reference_type' => VendorBill::class,
                'reference_id' => $bill->id,
                'description' => "Pelunasan Vendor Bill {$bill->bill_number}",
                'created_by' => $createdBy,
                'lines' => [
                    ['account_id' => $payableAccount->id, 'debit' => $bill->amount, 'credit' => 0],
                    ['account_id' => $paymentAccountId, 'debit' => 0, 'credit' => $bill->amount],
                ],
            ]);

            return $bill->fresh();
        });
    }

    protected function utangUsahaAccount(): ChartOfAccount
    {
        return ChartOfAccount::where('code', '201')->firstOrFail();
    }
}
