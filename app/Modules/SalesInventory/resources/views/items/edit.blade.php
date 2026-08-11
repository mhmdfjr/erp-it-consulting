<x-app-layout>
    <x-slot name="header">Edit Item</x-slot>

    <form method="POST" action="{{ route('sales.items.update', $item) }}" x-data="{ itemType: 'physical_good' }" class="max-w-xl space-y-4">
        @csrf
        @method('PUT')

        <x-input name="sku" label="SKU" required :value="old('sku') ?? $item->sku" />
        <x-input name="name" label="Nama Item" required :value="old('name') ?? $item->name" />

        <div>
            <label class="text-label text-slate-gray block mb-1">Tipe Item</label>
            <select name="item_type" x-model="itemType" class="w-full rounded-input border-border-gray">
                <option value="physical_good" {{ (old('item_type') ?? $item->item_type) === 'physical_good' ? 'selected' : '' }}>Barang (butuh stock tracking)</option>
                <option value="service" {{ (old('item_type') ?? $item->item_type) === 'service' ? 'selected' : '' }}>Jasa (tanpa stock tracking)</option>
            </select>
        </div>

        <div>
            <label class="text-label text-slate-gray block mb-1">Kategori</label>
            <select name="item_category_id" class="w-full rounded-input border-border-gray">
                <option value="">-- Tanpa kategori --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ (old('item_category_id') ?? $item->item_category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <x-input name="unit_of_measure" label="Satuan (pcs, unit, package, dst)" required :value="old('unit_of_measure') ?? $item->unit_of_measure" />
        <x-input name="unit_price" type="number" step="0.01" label="Harga Jual" required :value="old('unit_price') ?? $item->unit_price" />

        <div x-show="itemType === 'physical_good'">
            <x-input name="cost_price" type="number" step="0.01" label="Harga Modal (cost_price)" :value="old('cost_price') ?? $item->cost_price" />
        </div>

        <div class="flex gap-2">
            <x-button type="submit" variant="primary">Simpan</x-button>
            <x-button variant="danger" href="{{ route('sales.items.index') }}">Batal</x-button>
            <x-button variant="secondary" href="{{ route('sales.stock.movements', $item) }}">Adjust Stock</x-button>
        </div>
    </form>
</x-app-layout>
