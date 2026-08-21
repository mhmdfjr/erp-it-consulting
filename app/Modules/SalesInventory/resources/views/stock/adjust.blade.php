<x-app-layout>
    <x-slot name="header">
        Penyesuaian Stok: {{ $item->name }}
    </x-slot>

    @php
        $onHand = $item->stockLevel->quantity_on_hand ?? 0;
        $reserved = $item->stockLevel->quantity_reserved ?? 0;
        $available = max(0, $onHand - $reserved);
        $hasNoteError = $errors->has('note');
    @endphp

    <div class="max-w-4xl space-y-6">
        {{-- Item Stock Context Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-card bg-primary-tint text-primary flex items-center justify-center shrink-0 shadow-subtle">
                    <x-dynamic-component component="lucide-boxes" class="w-6 h-6" />
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="font-mono text-caption font-bold text-primary bg-primary-tint/60 px-2 py-0.5 rounded-input border border-primary/20">
                            {{ $item->sku }}
                        </span>
                        <h2 class="text-heading-sm font-bold text-ink-black tracking-tight">{{ $item->name }}</h2>
                    </div>
                    <p class="text-caption text-slate-gray mt-1 font-medium">
                        Satuan Unit: <strong class="text-ink-black uppercase">{{ $item->unit_of_measure }}</strong>
                    </p>
                </div>
            </div>

            {{-- Quick Stock Metrics Mini Badge --}}
            <div class="flex items-center gap-3 bg-fog-white border border-border-gray/80 rounded-card p-3 self-start md:self-auto">
                <div class="text-center px-2">
                    <span class="text-[10px] font-bold text-ash-gray uppercase tracking-wider block">Fisik (On Hand)</span>
                    <span class="text-body-sm font-bold text-ink-black tabular-nums">{{ (int) floor($onHand) }}</span>
                </div>
                <div class="h-6 w-px bg-border-gray/80"></div>
                <div class="text-center px-2">
                    <span class="text-[10px] font-bold text-ash-gray uppercase tracking-wider block">Reservasi</span>
                    <span class="text-body-sm font-bold text-slate-gray tabular-nums">{{ (int) floor($reserved) }}</span>
                </div>
                <div class="h-6 w-px bg-border-gray/80"></div>
                <div class="text-center px-2">
                    <span class="text-[10px] font-bold text-ash-gray uppercase tracking-wider block">Tersedia</span>
                    <span class="text-body-sm font-bold text-success tabular-nums">{{ (int) floor($available) }}</span>
                </div>
            </div>
        </div>

        {{-- Adjustment Form Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle max-w-2xl">
            <div class="border-b border-border-gray/60 pb-4 mb-6">
                <h3 class="text-heading-sm font-semibold text-ink-black">Formulir Penyesuaian Manual</h3>
                <p class="text-caption text-slate-gray mt-0.5">Catat penambahan atau pengurangan stok fisik untuk keperluan opname atau koreksi rusak.</p>
            </div>

            <form method="POST" action="{{ route('sales.stock.adjust.store', $item) }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Arah Penyesuaian --}}
                    <div>
                        <label for="direction" class="block text-label font-medium text-slate-gray mb-1.5">Arah Penyesuaian <span class="text-danger">*</span></label>
                        <select
                            name="direction"
                            id="direction"
                            class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                        >
                            <option value="in">Tambah Stok Masuk (+ In)</option>
                            <option value="out">Kurangi Stok Keluar (- Out)</option>
                        </select>
                        @error('direction')
                            <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jumlah Kuantitas --}}
                    <div>
                        <label for="quantity" class="block text-label font-medium text-slate-gray mb-1.5">Jumlah Kuantitas <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            name="quantity"
                            id="quantity"
                            step="0.01"
                            min="0.01"
                            required
                            placeholder="0"
                            value="{{ old('quantity') }}"
                            class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body font-bold text-ink-black tabular-nums transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('quantity') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                        />
                        @error('quantity')
                            <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Reason Code --}}
                <div>
                    <label for="reason_code" class="block text-label font-medium text-slate-gray mb-1.5">Alasan Penyesuaian (Reason Code) <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="reason_code"
                        id="reason_code"
                        required
                        placeholder="Contoh: stock_opname, barang_rusak, retur_vendor, selisih_hitung"
                        value="{{ old('reason_code') }}"
                        class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('reason_code') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                    />
                    @error('reason_code')
                        <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Note --}}
                <div>
                    <label for="note" class="block text-label font-medium text-slate-gray mb-1.5">Catatan Tambahan (Opsional)</label>
                    <textarea
                        name="note"
                        id="note"
                        rows="3"
                        placeholder="Keterangan detail mengenai penyesuaian fisik stok ini"
                        class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $hasNoteError ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                    >{{ old('note') }}</textarea>
                    @error('note')
                        <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                    <x-button variant="secondary" size="md" href="{{ route('sales.stock.movements', $item) }}">
                        Batal
                    </x-button>
                    <x-button variant="primary" size="md" type="submit">
                        Simpan Penyesuaian
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
