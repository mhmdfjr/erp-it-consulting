<x-app-layout>
    <x-slot name="header">Riwayat Stok: {{ $item->name }}</x-slot>

    <div class="flex justify-between items-center mb-4">
        <div class="text-body-sm text-slate-gray">
            On hand: <span class="tabular-nums font-medium">{{ $item->stockLevel->quantity_on_hand ?? 0 }}</span>
            &middot;
            Reserved: <span class="tabular-nums font-medium">{{ $item->stockLevel->quantity_reserved ?? 0 }}</span>
        </div>
        <div class="flex gap-2">
            <x-button variant="secondary" href="{{ route('sales.items.edit', $item) }}">Kembali ke Item</x-button>
            <x-button variant="primary" href="{{ route('sales.stock.adjust', $item) }}">+ Adjust Stock</x-button>
        </div>
    </div>

    <x-data-table :headers="['Tanggal', 'Tipe', 'Qty', 'Reason / Referensi']" :empty="$movements->isEmpty()">
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada riwayat stok</p>
                <p class="text-body-sm text-slate-gray">Belum ada pergerakan stok untuk item ini.</p>
            </div>
        </x-slot>

        @foreach ($movements as $movement)
            <tr>
                <td class="px-4 py-3">{{ $movement->created_at->format('d M Y H:i') }}</td>
                <td class="px-4 py-3">
                    <x-badge status="{{ str_contains($movement->movement_type, 'in') ? 'success' : 'warning' }}">
                        {{ $movement->movement_type }}
                    </x-badge>
                </td>
                <td class="px-4 py-3 tabular-nums">{{ $movement->quantity }}</td>
                <td class="px-4 py-3 text-body-sm text-slate-gray">
                    {{ $movement->reason_code ?? $movement->reference_type }}
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $movements->links() }}
        </x-slot>
    </x-data-table>

    {{ $movements->links() }}
</x-app-layout>
