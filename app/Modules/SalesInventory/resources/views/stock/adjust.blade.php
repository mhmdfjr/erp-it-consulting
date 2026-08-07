<x-app-layout>
    <x-slot name="header">Stock Adjustment: {{ $item->name }}</x-slot>

    <div class="mb-4 text-body-sm text-slate-gray">
        Stok saat ini: <span class="tabular-nums font-medium">{{ $item->stockLevel->quantity_on_hand ?? 0 }}</span>
    </div>

    <form method="POST" action="{{ route('sales.stock.adjust.store', $item) }}" class="max-w-lg space-y-4">
        @csrf

        <div>
            <label class="text-label text-slate-gray block mb-1">Arah Penyesuaian</label>
            <select name="direction" class="w-full rounded-input border-border-gray">
                <option value="in">Tambah Stok (in)</option>
                <option value="out">Kurangi Stok (out)</option>
            </select>
        </div>

        <x-input name="quantity" type="number" step="0.01" label="Quantity" required />


        <x-input name="reason_code" label="Alasan (reason code)" required
            placeholder="mis. stock_opname, barang_rusak, retur_vendor" />

        <div>
            <label class="text-label text-slate-gray block mb-1">Catatan (opsional)</label>
            <textarea name="note" rows="3" class="w-full rounded-input border-border-gray"></textarea>
        </div>

        <div class="flex gap-2">
            <x-button type="submit" variant="primary">Simpan Penyesuaian</x-button>
            <x-button variant="danger" href="{{ route('sales.stock.movements', $item) }}">Batal</x-button>
        </div>
    </form>
</x-app-layout>
