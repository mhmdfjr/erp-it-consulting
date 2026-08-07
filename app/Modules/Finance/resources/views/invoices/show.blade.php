<x-app-layout>
    <x-slot name="header">Invoice {{ $invoice->invoice_number }}</x-slot>

    <div class="flex items-center justify-between mb-4">
        <x-badge status="{{ $invoice->status === 'paid' ? 'success' : 'warning' }}">
            {{ ucfirst($invoice->status) }}
        </x-badge>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
            <p class="text-label text-slate-gray">Customer</p>
            <p class="text-body">{{ $invoice->salesOrder->customer->name }}</p>
        </div>
        <div>
            <p class="text-label text-slate-gray">Sales Order</p>
            <a href="{{ route('sales.orders.show', $invoice->salesOrder) }}" class="text-body text-info underline">
                {{ $invoice->salesOrder->order_number }}
            </a>
        </div>
        <div>
            <p class="text-label text-slate-gray">Tanggal Invoice</p>
            <p class="text-body">{{ $invoice->invoice_date->format('d M Y') }}</p>
        </div>
        <div>
            <p class="text-label text-slate-gray">Jatuh Tempo</p>
            <p class="text-body">{{ $invoice->due_date->format('d M Y') }}</p>
        </div>
        <div>
            <p class="text-label text-slate-gray">Total Tagihan</p>
            <p class="text-body-lg font-medium tabular-nums">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</p>
        </div>
        @php $remaining = bcsub((string) $invoice->amount, (string) $invoice->payments->sum('amount'), 2); @endphp
        <div>
            <p class="text-label text-slate-gray">Sisa Tagihan</p>
            <p class="text-body-lg font-medium tabular-nums {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                Rp {{ number_format($remaining, 0, ',', '.') }}
            </p>
        </div>
    </div>

    <h3 class="text-heading-sm mb-2">Riwayat Pembayaran</h3>
    <x-data-table :headers="['Tanggal', 'Jumlah', 'Metode', 'Referensi']" :empty="$invoice->payments->isEmpty()">
        <x-slot name="emptyState">
            <p class="text-body-sm text-slate-gray">Belum ada pembayaran tercatat.</p>
        </x-slot>

        @foreach ($invoice->payments as $payment)
            <tr>
                <td class="px-4 py-3">{{ $payment->payment_date->format('d M Y') }}</td>
                <td class="px-4 py-3 text-right tabular-nums">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                <td class="px-4 py-3">{{ $payment->payment_method ?? '-' }}</td>
                <td class="px-4 py-3">{{ $payment->reference_number ?? '-' }}</td>
            </tr>
        @endforeach
    </x-data-table>

    @can('finance.invoice.pay', $invoice)
        @if ($invoice->status !== 'paid')
            <h3 class="text-heading-sm mt-6 mb-2">Record Payment</h3>
            <form method="POST" action="{{ route('finance.invoices.payments.store', $invoice) }}" class="max-w-md space-y-4">
                @csrf
                <x-input name="payment_date" type="date" label="Tanggal Bayar" required :value="now()->format('Y-m-d')" />
                <x-input name="amount" type="number" step="0.01" label="Jumlah" required :value="$remaining" />

                <div>
                    <label class="text-label text-slate-gray block mb-1">Metode Pembayaran</label>
                    <select name="payment_method" class="w-full rounded-input border-border-gray">
                        <option value="transfer">Transfer</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>

                <x-input name="reference_number" label="No. Referensi (opsional)" />

                @error('amount') <p class="text-caption text-danger">{{ $message }}</p> @enderror

                <x-button type="submit" variant="primary">Catat Pembayaran</x-button>
            </form>
        @endif
    @endcan
</x-app-layout>
