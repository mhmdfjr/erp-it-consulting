<x-app-layout>
    <x-slot name="header">
        Katalog Barang & Jasa
    </x-slot>

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="mb-5 rounded-input bg-success-bg border border-success/20 text-success px-4 py-3 text-body-sm flex items-center gap-2">
            <x-dynamic-component component="lucide-check-circle-2" class="w-4 h-4 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-5 rounded-input bg-danger-bg border border-danger/20 text-danger px-4 py-3 text-body-sm flex items-center gap-2">
            <x-dynamic-component component="lucide-alert-circle" class="w-4 h-4 shrink-0" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Items Catalog Table Card --}}
    <x-data-table
        title="Daftar Produk & Layanan"
        subtitle="Kelola katalog produk fisik (dengan stok inventaris) dan jasa konsultasi/layanan IT"
        :headers="['Item & SKU', 'Kategori', 'Tipe Item', 'Harga Jual', 'Satuan', 'Aksi']"
        :empty="$items->isEmpty()"
    >
        <x-slot name="action">
            @can('sales.item.create')
                <div class="flex items-center gap-2.5">
                    <x-button variant="secondary" size="sm" href="{{ route('sales.categories.index') }}" class="gap-1.5">
                        <x-dynamic-component component="lucide-tags" class="w-4 h-4" />
                        <span>Kelola Kategori</span>
                    </x-button>

                    <x-button variant="primary" size="sm" href="{{ route('sales.items.create') }}" class="gap-1.5">
                        <x-dynamic-component component="lucide-plus-circle" class="w-4 h-4" />
                        <span>Tambah Item</span>
                    </x-button>
                </div>
            @endcan
        </x-slot>

        <x-slot name="emptyState">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                    <x-dynamic-component component="lucide-package" class="w-6 h-6" />
                </div>
                <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Item di Katalog</h4>
                <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Tambahkan produk fisik atau layanan jasa pertama untuk mulai membuat Sales Order.</p>
                @can('sales.item.create')
                    <x-button variant="primary" href="{{ route('sales.items.create') }}" size="sm">
                        + Tambah Barang & Jasa
                    </x-button>
                @endcan
            </div>
        </x-slot>

        @foreach ($items as $item)
            <tr class="hover:bg-mist-gray/40 transition-colors">
                {{-- Nama Item & SKU --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-card {{ $item->item_type === 'physical_good' ? 'bg-primary-tint text-primary' : 'bg-accent-peach-tint text-[#c2410c]' }} font-bold flex items-center justify-center shrink-0 shadow-subtle">
                            <x-dynamic-component :component="$item->item_type === 'physical_good' ? 'lucide-package' : 'lucide-sparkles'" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-body-sm font-semibold text-ink-black leading-tight">{{ $item->name }}</p>
                            <p class="font-mono text-caption text-slate-gray tabular-nums mt-0.5">
                                SKU: <span class="font-semibold text-ink-black">{{ $item->sku }}</span>
                            </p>
                        </div>
                    </div>
                </td>

                {{-- Kategori --}}
                <td class="px-6 py-4">
                    @if ($item->category)
                        <span class="inline-flex items-center gap-1.5 text-caption font-semibold text-slate-gray bg-fog-white border border-border-gray/80 px-2.5 py-0.5 rounded-badge">
                            {{ $item->category->name }}
                        </span>
                    @else
                        <span class="text-caption text-ash-gray">-</span>
                    @endif
                </td>

                {{-- Tipe Item --}}
                <td class="px-6 py-4">
                    <x-badge
                        :status="$item->item_type === 'physical_good' ? 'primary' : 'peach'"
                        variant="subtle"
                    >
                        {{ $item->item_type === 'physical_good' ? 'Barang Fisik' : 'Jasa Layanan' }}
                    </x-badge>
                </td>

                {{-- Harga Jual --}}
                <td class="px-6 py-4 text-body-sm font-bold text-ink-black tabular-nums">
                    Rp {{ number_format($item->unit_price, 2, ',', '.') }}
                </td>

                {{-- Satuan --}}
                <td class="px-6 py-4 text-caption font-medium text-slate-gray uppercase tracking-wider">
                    {{ $item->unit_of_measure }}
                </td>

                {{-- Aksi --}}
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="{{ route('sales.items.edit', $item) }}"
                           class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                           title="Edit Item">
                            <x-dynamic-component component="lucide-pencil" class="w-4 h-4" />
                        </a>

                        @if ($item->item_type === 'physical_good')
                            <a href="{{ route('sales.stock.movements', $item) }}"
                               class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                               title="Riwayat & Penyesuaian Stok">
                                <x-dynamic-component component="lucide-boxes" class="w-4 h-4" />
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $items->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
