<x-app-layout>
    <x-slot name="header">
        Edit Item: {{ $item->name }}
    </x-slot>

    <div class="max-w-3xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle"
         x-data="{ itemType: '{{ old('item_type', $item->item_type) }}' }">
        <div class="border-b border-border-gray/60 pb-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-heading-sm font-semibold text-ink-black">Perbarui Data Item</h3>
                <p class="text-caption text-slate-gray mt-0.5">Ubah informasi harga, kategori, dan parameter katalog produk.</p>
            </div>
            @if ($item->item_type === 'physical_good')
                <x-button variant="secondary" size="sm" href="{{ route('sales.stock.movements', $item) }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-sliders" class="w-4 h-4" />
                    <span>Penyesuaian Stok</span>
                </x-button>
            @endif
        </div>

        <form method="POST" action="{{ route('sales.items.update', $item) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- SKU --}}
                <x-input
                    name="sku"
                    label="Kode SKU Produk / Jasa"
                    required
                    :value="old('sku', $item->sku)"
                />

                {{-- Tipe Item --}}
                <div>
                    <label for="item_type" class="block text-label font-medium text-slate-gray mb-1.5">Tipe Item <span class="text-danger">*</span></label>
                    <select
                        name="item_type"
                        id="item_type"
                        x-model="itemType"
                        class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                    >
                        <option value="physical_good" @selected(old('item_type', $item->item_type) === 'physical_good')>Barang Fisik (Perlu Tracking Stok)</option>
                        <option value="service" @selected(old('item_type', $item->item_type) === 'service')>Jasa / Konsultasi (Tanpa Stok)</option>
                    </select>
                </div>
            </div>

            {{-- Nama Item --}}
            <x-input
                name="name"
                label="Nama Item / Layanan"
                required
                :value="old('name', $item->name)"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Kategori --}}
                <div>
                    <label for="item_category_id" class="block text-label font-medium text-slate-gray mb-1.5">Kategori Katalog</label>
                    <select
                        name="item_category_id"
                        id="item_category_id"
                        class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                    >
                        <option value="">-- Tanpa Kategori (Umum) --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('item_category_id', $item->item_category_id) == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Satuan Ukuran --}}
                <x-input
                    name="unit_of_measure"
                    label="Satuan Unit (UoM)"
                    required
                    :value="old('unit_of_measure', $item->unit_of_measure)"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                {{-- Harga Jual --}}
                <div>
                    <x-input
                        name="unit_price"
                        type="number"
                        step="0.01"
                        label="Harga Jual Satuan (Rp)"
                        required
                        :value="old('unit_price', $item->unit_price)"
                    />
                </div>

                {{-- Harga Pokok / Modal --}}
                <div x-show="itemType === 'physical_good'" x-transition>
                    <x-input
                        name="cost_price"
                        type="number"
                        step="0.01"
                        label="Harga Pokok Penjualan / Modal (Rp)"
                        :value="old('cost_price', $item->cost_price)"
                    />
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('sales.items.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Simpan Perubahan
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
