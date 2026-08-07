<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Katalog Barang & Jasa</h1>
            @can('sales.item.create')
                <div class="flex justify-end gap-2">
                    <x-button variant="secondary" href="{{ route('sales.categories.index') }}">
                        Kelola Kategori
                    </x-button>
                    <x-button variant="primary" href="{{ route('sales.items.create') }}">
                        + Tambah Item
                    </x-button>
                </div>
            @endcan
        </div>
    </x-slot>

    <x-data-table :headers="['SKU', 'Nama', 'Tipe', 'Harga Jual', 'Aksi']" :empty="$items->isEmpty()">
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada item</p>
                <p class="text-body-sm text-slate-gray">Tambahkan produk atau jasa pertama ke catalog.</p>
                <x-button variant="primary" href="{{ route('sales.items.create') }}">+ Tambah Item</x-button>
            </div>
        </x-slot>

        @foreach ($items as $item)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3">{{ $item->sku }}</td>
                <td class="px-4 py-3">{{ $item->name }}</td>
                <td class="px-4 py-3">
                    <x-badge status="{{ $item->item_type === 'physical_good' ? 'info' : 'success' }}">
                        {{ $item->item_type === 'physical_good' ? 'Barang' : 'Jasa' }}
                    </x-badge>
                </td>
                <td class="px-4 py-3 tabular-nums">
                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('sales.items.edit', $item) }}" class="text-info hover:opacity-70">
                        <x-lucide-pencil class="w-4 h-4" />
                    </a>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $items->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
