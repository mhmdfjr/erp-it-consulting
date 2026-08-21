<x-app-layout>
    <x-slot name="header">
        Tambah Barang & Jasa Baru
    </x-slot>

    <div class="max-w-3xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle"
         x-data="{ itemType: '{{ old('item_type', 'physical_good') }}' }">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Informasi Produk / Layanan</h3>
            <p class="text-caption text-slate-gray mt-0.5">Daftarkan item baru untuk inventaris stok gudang atau layanan jasa konsultasi.</p>
        </div>

        <form method="POST" action="{{ route('sales.items.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- SKU --}}
                <x-input
                    name="sku"
                    label="Kode SKU Produk / Jasa"
                    placeholder="Contoh: IT-SRV-001"
                    required
                    :value="old('sku') ?? ''"
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
                        <option value="physical_good">Barang Fisik (Perlu Tracking Stok)</option>
                        <option value="service">Jasa / Konsultasi (Tanpa Stok)</option>
                    </select>
                </div>
            </div>

            {{-- Nama Item --}}
            <x-input
                name="name"
                label="Nama Item / Layanan"
                placeholder="Contoh: Server Rack 42U atau Jasa Audit Keamanan Jaringan"
                required
                :value="old('name') ?? ''"
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
                            <option value="{{ $category->id }}" @selected(old('item_category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Satuan Ukuran --}}
                <x-input
                    name="unit_of_measure"
                    label="Satuan Unit (UoM)"
                    placeholder="Contoh: pcs, unit, lisensi, jam, paket"
                    required
                    :value="old('unit_of_measure') ?? ''"
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
                        placeholder="0.00"
                        required
                        :value="old('unit_price') ?? ''"
                    />
                </div>

                {{-- Harga Pokok / Modal (Cost Price) --}}
                <div x-show="itemType === 'physical_good'" x-transition>
                    <x-input
                        name="cost_price"
                        type="number"
                        step="0.01"
                        label="Harga Pokok Penjualan / Modal (Rp)"
                        placeholder="0.00"
                        :value="old('cost_price') ?? ''"
                    />
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('sales.items.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Simpan Item
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
