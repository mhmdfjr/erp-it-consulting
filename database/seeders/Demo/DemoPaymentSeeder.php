<?php
// database/seeders/Demo/DemoPaymentSeeder.php

namespace Database\Seeders\Demo;

use App\Models\User;
use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\Payment;
use App\Modules\Finance\Services\JournalEntryService;
use App\Modules\SalesInventory\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DemoPaymentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            abort(403, 'DemoPaymentSeeder tidak boleh dijalankan di production.');
        }

        $financeUser = User::where('email', 'fajar.finance@test.local')->firstOrFail();
        Auth::loginUsingId($financeUser->id);

        $journalEntryService = app(JournalEntryService::class);
        $customers = Customer::all()->keyBy('name');

        foreach ($this->paymentScenarios($customers) as $scenario) {
            $invoice = $this->resolveInvoice($scenario['customer']);

            if (! $invoice) {
                $this->command->warn("Invoice untuk customer '{$scenario['customer']->name}' tidak ditemukan, skip.");
                continue;
            }

            foreach ($scenario['payments'] as $paymentData) {
                $paymentData['amount'] = $this->resolveAmount($paymentData['amount'], $invoice);
                $this->recordPayment($invoice, $paymentData, $journalEntryService);
                $invoice->refresh();
            }
        }

        Auth::logout();

        $this->command->info('Demo Payment selesai.');
    }

    private function resolveInvoice(Customer $customer): ?Invoice
    {
        return Invoice::whereHas('salesOrder', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id)->where('status', 'completed');
        })->first();
    }

    private function recordPayment(Invoice $invoice, array $data, JournalEntryService $journalEntryService): void
    {
        $remaining = bcsub((string) $invoice->amount, (string) $invoice->payments()->sum('amount'), 2);

        if (bccomp((string) $data['amount'], $remaining, 2) > 0) {
            $this->command->warn(sprintf(
                'Payment %s untuk invoice %s melebihi sisa tagihan (%s), dilewati.',
                $data['amount'],
                $invoice->invoice_number,
                $remaining
            ));
            return;
        }

        DB::transaction(function () use ($invoice, $data, $journalEntryService) {
            $payment = $invoice->payments()->create($data);

            $cashAccountCode = $data['payment_method'] === 'cash' ? '101' : '102';
            $cashAccountId = ChartOfAccount::where('code', $cashAccountCode)->firstOrFail()->id;
            $receivableAccountId = ChartOfAccount::where('code', '103')->firstOrFail()->id;

            $journalEntryService->createEntry([
                'entry_date' => $payment->payment_date,
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'description' => "Pelunasan invoice {$invoice->invoice_number} (data demo)",
                'created_by' => Auth::id(),
                'lines' => [
                    [
                        'account_id' => $cashAccountId,
                        'debit' => $payment->amount,
                        'credit' => 0,
                        'description' => "Penerimaan pembayaran invoice {$invoice->invoice_number}",
                    ],
                    [
                        'account_id' => $receivableAccountId,
                        'debit' => 0,
                        'credit' => $payment->amount,
                        'description' => "Pelunasan piutang invoice {$invoice->invoice_number}",
                    ],
                ],
            ]);

            $totalPaid = $invoice->payments()->sum('amount');
            if (bccomp((string) $totalPaid, (string) $invoice->amount, 2) >= 0) {
                $invoice->update(['status' => 'paid', 'paid_at' => now()]);
            }
        });
    }

    private function paymentScenarios($customers): array
    {
        $today = now();

        return [
            // Full payment sekali bayar, transfer.
            [
                'customer' => $customers['PT Maju Bersama Sejahtera'],
                'payments' => [
                    ['payment_date' => $today->copy()->subDays(15)->toDateString(), 'amount' => null, 'payment_method' => 'transfer', 'reference_number' => 'TRF-DEMO-0001'],
                ],
            ],
            // Full payment sekali bayar, cash -> uji pemilihan akun 101 vs 102.
            [
                'customer' => $customers['PT Sinergi Manufaktur'],
                'payments' => [
                    ['payment_date' => $today->copy()->subDays(10)->toDateString(), 'amount' => null, 'payment_method' => 'cash', 'reference_number' => null],
                ],
            ],
            // Partial payment saja, sengaja tidak digenapi -> invoice tetap unpaid.
            [
                'customer' => $customers['PT Cahaya Retail Indonesia'],
                'payments' => [
                    ['payment_date' => $today->copy()->subDays(8)->toDateString(), 'amount' => 'partial_50', 'payment_method' => 'transfer', 'reference_number' => 'TRF-DEMO-0002'],
                ],
            ],
            // 2x partial payment yang menggenapi jadi full paid.
            [
                'customer' => $customers['CV Berkah Logistik'],
                'payments' => [
                    ['payment_date' => $today->copy()->subDays(9)->toDateString(), 'amount' => 'partial_60', 'payment_method' => 'transfer', 'reference_number' => 'TRF-DEMO-0003'],
                    ['payment_date' => $today->copy()->subDays(3)->toDateString(), 'amount' => 'remaining', 'payment_method' => 'transfer', 'reference_number' => 'TRF-DEMO-0004'],
                ],
            ],
            // PT Konstruksi Baja Perkasa, PT Agro Teknologi Nusantara, dan PT Griya Sehat
            // Farma sengaja TIDAK dibayar sama sekali -> outstanding invoice
        ];
    }

    private function resolveAmount(?string $keyword, Invoice $invoice): string
    {
        $remaining = bcsub((string) $invoice->amount, (string) $invoice->payments()->sum('amount'), 2);

        return match ($keyword) {
            null => $remaining,
            'partial_50' => bcmul($remaining, '0.5', 2),
            'partial_60' => bcmul($remaining, '0.6', 2),
            'remaining' => $remaining,
            default => $remaining,
        };
    }
}
