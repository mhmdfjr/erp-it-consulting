<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Pesanan Penjualan</h1>
            @can('sales.order.create')
                <x-button variant="primary" href="{{ route('sales.orders.create') }}">
                    + Buat Sales Order
                </x-button>
            @endcan
        </div>
    </x-slot>

    <x-data-table :headers="['No. Order', 'Customer', 'Tanggal', 'Status', 'Total']" :empty="$orders->isEmpty()">
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada sales order</p>
                <p class="text-body-sm text-slate-gray">Buat sales order pertama.</p>
                <x-button variant="primary" href="{{ route('sales.orders.create') }}">+ Buat Sales Order</x-button>
            </div>
        </x-slot>

        @foreach ($orders as $order)
            <tr class="hover:bg-mist-gray cursor-pointer" onclick="window.location='{{ route('sales.orders.show', $order) }}'">
                <td class="px-4 py-3">{{ $order->order_number }}</td>
                <td class="px-4 py-3">{{ $order->customer->name }}</td>
                <td class="px-4 py-3">{{ $order->order_date->format('d M Y') }}</td>
                <td class="px-4 py-3">
                    <x-badge status="{{
                        match($order->status) {
                            'draft' => 'info',
                            'completed', 'invoiced' => 'success',
                            'cancelled' => 'danger',
                            default => 'info',
                        }
                    }}">
                        {{ ucfirst($order->status) }}
                    </x-badge>
                </td>
                <td class="px-4 py-3 tabular-nums">
                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $orders->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
