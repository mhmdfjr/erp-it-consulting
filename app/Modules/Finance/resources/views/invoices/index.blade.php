<x-app-layout>
    <x-slot name="header">Invoice</x-slot>

    <x-data-table :headers="['No. Invoice', 'Customer', 'Jatuh Tempo', 'Status', 'Jumlah']" :empty="$invoices->isEmpty()">
        <x-slot name="emptyState">
            <p class="text-body-sm text-slate-gray">Belum ada invoice. Invoice terbit otomatis saat Sales Order di-complete.</p>
        </x-slot>

        @foreach ($invoices as $invoice)
            <tr class="hover:bg-mist-gray cursor-pointer" onclick="window.location='{{ route('finance.invoices.show', $invoice) }}'">
                <td class="px-4 py-3">{{ $invoice->invoice_number }}</td>
                <td class="px-4 py-3">{{ $invoice->salesOrder->customer->name }}</td>
                <td class="px-4 py-3">{{ $invoice->due_date->format('d M Y') }}</td>
                <td class="px-4 py-3">
                    <x-badge status="{{ $invoice->status === 'paid' ? 'success' : ($invoice->due_date->isPast() ? 'danger' : 'warning') }}">
                        {{ ucfirst($invoice->status) }}
                    </x-badge>
                </td>
                <td class="px-4 py-3 text-right tabular-nums">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
        @endforeach

        <x-slot name="pagination">{{ $invoices->links() }}</x-slot>
    </x-data-table>
</x-app-layout>
