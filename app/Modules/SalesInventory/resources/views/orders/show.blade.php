<x-app-layout>
    <x-slot name="header">Sales Order {{ $order->order_number }}</x-slot>

    <div class="flex items-center justify-between mb-4">
        <x-badge status="{{
            match($order->status) {
                'draft', 'confirmed' => 'info',
                'completed', 'invoiced' => 'success',
                'cancelled' => 'danger',
                default => 'info',
            }
        }}">
            {{ ucfirst($order->status) }}
        </x-badge>

        <div class="flex gap-2">
            <x-button variant="secondary" href="{{ route('sales.orders.index') }}">Kembali ke Orders</x-button>

            @can('sales.order.complete', $order)
                @if ($order->status !== 'completed' && $order->status !== 'cancelled')
                    <form method="POST" action="{{ route('sales.orders.complete', $order) }}">
                        @csrf
                        <x-button type="submit" variant="primary">Complete Order</x-button>
                    </form>
                @endif
            @endcan

            @can('sales.order.cancel', $order)
                @if ($order->isCancellable())
                    <x-button variant="danger" type="button" onclick="openCancelModal()">
                        Cancel Order
                    </x-button>
                @endif
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
            <p class="text-label text-slate-gray">Customer</p>
            <p class="text-body">{{ $order->customer->name }}</p>
        </div>
        <div>
            <p class="text-label text-slate-gray">Tanggal Order</p>
            <p class="text-body">{{ $order->order_date->format('d M Y') }}</p>
        </div>
        @if ($order->invoice)
            <div>
                <p class="text-label text-slate-gray">Invoice</p>
                <p class="text-body">{{ $order->invoice->invoice_number }} ({{ $order->invoice->status }})</p>
            </div>
        @endif
        @if ($order->status === 'cancelled')
            <div class="col-span-2">
                <p class="text-label text-slate-gray">Alasan Pembatalan</p>
                <p class="text-body text-danger">{{ $order->cancel_reason }}</p>
            </div>
        @endif
    </div>

    <x-data-table :headers="['Item', 'Qty', 'Harga', 'Subtotal']" :empty="false">
        @foreach ($order->items as $line)
            <tr>
                <td class="px-4 py-3">{{ $line->item->name }}</td>
                <td class="px-4 py-3 text-right tabular-nums">{{ $line->quantity }}</td>
                <td class="px-4 py-3 text-right tabular-nums">Rp {{ number_format($line->unit_price, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-right tabular-nums">Rp {{ number_format($line->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="3" class="px-4 py-3 text-right font-medium">Total</td>
            <td class="px-4 py-3 text-right tabular-nums font-medium">
                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
            </td>
        </tr>
    </x-data-table>

    <div id="cancel-order-modal" class="hidden fixed inset-0 bg-black/40 items-center justify-center z-50">
        <div class="bg-paper-white rounded-card shadow-elevated max-w-md w-full p-6">
            <h3 class="text-heading-sm mb-2">Batalkan Sales Order</h3>
            <p class="text-body-sm text-slate-gray mb-4">
                Stok yang sudah direservasi untuk order ini akan dilepas. Tindakan ini tidak bisa dibatalkan.
            </p>

            <form method="POST" action="{{ route('sales.orders.cancel', $order) }}">
                @csrf
                <label class="text-label text-slate-gray block mb-1">Alasan Pembatalan</label>
                <textarea name="reason" required rows="3"
                    class="w-full rounded-input border-border-gray mb-1"></textarea>
                @error('reason') <p class="text-caption text-danger mb-3">{{ $message }}</p> @enderror

                <div class="flex justify-end gap-2 mt-4">
                    <x-button type="button" variant="secondary" onclick="closeCancelModal()">Batal</x-button>
                    <x-button type="submit" variant="danger">Ya, Batalkan Order</x-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCancelModal() {
            const modal = document.getElementById('cancel-order-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function closeCancelModal() {
            const modal = document.getElementById('cancel-order-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</x-app-layout>
