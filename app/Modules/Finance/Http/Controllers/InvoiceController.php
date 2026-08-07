<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Http\Requests\StorePaymentRequest;
use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Services\JournalEntryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        $this->authorize('finance.invoice.view', Invoice::class);

        $invoices = Invoice::with('salesOrder.customer')->latest('invoice_date')->paginate(20);

        return view('finance::invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('finance.invoice.view', $invoice);

        $invoice->load(['salesOrder.customer', 'payments']);

        return view('finance::invoices.show', compact('invoice'));
    }

    // Status invoice jadi 'paid' setelah total payments >= amount, sekaligus entri jurnal agar balance
    public function storePayment(StorePaymentRequest $request, Invoice $invoice, JournalEntryService $journalEntryService)
    {
        $this->authorize('finance.invoice.pay', $invoice);

        $data = $request->validated();

        DB::transaction(function () use ($invoice, $data, $journalEntryService) {
            $payment = $invoice->payments()->create($data);

            $cashAccountCode = $data['payment_method'] === 'cash' ? '101' : '102';
            $cashAccountId = ChartOfAccount::where('code', $cashAccountCode)->firstOrFail()->id;
            $receivableAccountId = ChartOfAccount::where('code', '103')->firstOrFail()->id;

            $journalEntryService->createEntry([
                'entry_date' => $payment->payment_date,
                'reference_type' => \App\Modules\Finance\Models\Payment::class,
                'reference_id' => $payment->id,
                'description' => "Pelunasan invoice {$invoice->invoice_number}",
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

        return redirect()
            ->route('finance.invoices.show', $invoice)
            ->with('success', 'Pembayaran berhasil dicatat.');
    }
}
