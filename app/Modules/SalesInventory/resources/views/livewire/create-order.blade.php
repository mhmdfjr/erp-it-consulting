<div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
    <div class="border-b border-border-gray/60 pb-4 mb-6">
        <h3 class="text-heading-sm font-semibold text-ink-black">Formulir Pesanan Penjualan</h3>
        <p class="text-caption text-slate-gray mt-0.5">Pilih pelanggan dan tentukan item yang dipesan beserta kuantitasnya.</p>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{-- Header Form Controls --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-label font-medium text-slate-gray mb-1.5">Pelanggan (Customer) <span class="text-danger">*</span></label>
                <select
                    wire:model="customer_id"
                    class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('customer_id') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                >
                    <option value="">-- Pilih Customer --</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ ucfirst($customer->customer_type) }})</option>
                    @endforeach
                </select>
                @error('customer_id')
                    <p class="text-caption font-medium text-danger mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-label font-medium text-slate-gray mb-1.5">Tanggal Pesanan <span class="text-danger">*</span></label>
                <input
                    type="date"
                    wire:model="order_date"
                    class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                />
                @error('order_date')
                    <p class="text-caption font-medium text-danger mt-1.5">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @error('items')
            <div class="rounded-input bg-danger-bg border border-danger/20 text-danger px-4 py-2.5 text-caption font-medium flex items-center gap-2">
                <x-dynamic-component component="lucide-alert-circle" class="w-4 h-4 shrink-0" />
                <span>{{ $message }}</span>
            </div>
        @enderror

        {{-- Items Interactive Table --}}
        <div class="overflow-x-auto border border-border-gray/80 rounded-card shadow-subtle">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border-gray/60 bg-fog-white/60">
                        <th scope="col" class="px-4 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider min-w-[240px]">Item / Layanan <span class="text-danger">*</span></th>
                        <th scope="col" class="px-4 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider text-right w-[120px]">Qty <span class="text-danger">*</span></th>
                        <th scope="col" class="px-4 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider text-right w-[130px]">Stok Ada</th>
                        <th scope="col" class="px-4 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider text-right w-[180px]">Harga Satuan</th>
                        <th scope="col" class="px-4 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider text-right w-[180px]">Subtotal</th>
                        <th scope="col" class="px-3 py-3.5 text-center w-[50px]"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-gray/50">
                    @foreach ($this->items as $key => $line)
                        <tr wire:key="order-item-{{ $key }}" class="hover:bg-mist-gray/30 transition-colors">
                            {{-- Item Picker --}}
                            <td class="p-3">
                                <select
                                    wire:model.live="items.{{ $key }}.item_id"
                                    wire:change="itemSelected({{ $key }})"
                                    class="w-full rounded-input border bg-paper-white px-3 py-1.5 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has("items.{$key}.item_id") ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                                >
                                    <option value="">-- Pilih Item --</option>
                                    @foreach ($availableItems as $item)
                                        <option value="{{ $item->id }}">
                                            [{{ $item->item_type === 'physical_good' ? 'Barang' : 'Jasa' }}] {{ $item->name }} ({{ $item->sku }})
                                        </option>
                                    @endforeach
                                </select>
                                @error("items.{$key}.item_id")
                                    <p class="text-caption font-medium text-danger mt-1">{{ $message }}</p>
                                @enderror
                            </td>

                            {{-- Quantity Input --}}
                            <td class="p-3">
                                <input
                                    type="number"
                                    min="1"
                                    step="1"
                                    @if(!is_null($line['available_stock'] ?? null)) max="{{ (int) floor($line['available_stock']) }}" @endif
                                    wire:model.live="items.{{ $key }}.quantity"
                                    class="w-full text-right font-medium tabular-nums rounded-input border border-border-gray bg-paper-white px-3 py-1.5 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                                />
                            </td>

                            {{-- Available Stock Indicator --}}
                            <td class="p-3 text-right tabular-nums text-body-sm font-semibold {{ (!is_null($line['available_stock'] ?? null) && $line['available_stock'] <= 0) ? 'text-danger' : 'text-slate-gray' }}">
                                {{ is_null($line['available_stock'] ?? null) ? '∞' : (int) floor($line['available_stock']) }}
                            </td>

                            {{-- Unit Price --}}
                            <td class="p-3 text-right">
                                <span class="font-medium text-body-sm text-slate-gray tabular-nums">
                                    Rp {{ number_format($line['unit_price'] ?? 0, 2, ',', '.') }}
                                </span>
                            </td>

                            {{-- Subtotal --}}
                            <td class="p-3 text-right font-bold text-body-sm text-ink-black tabular-nums">
                                Rp {{ number_format($this->getSubtotal($line), 2, ',', '.') }}
                            </td>

                            {{-- Delete Row --}}
                            <td class="p-3 text-center">
                                <button
                                    type="button"
                                    wire:click="removeItem({{ $key }})"
                                    class="p-1.5 rounded-input text-danger hover:bg-danger-bg transition"
                                    title="Hapus Baris"
                                >
                                    <x-dynamic-component component="lucide-trash-2" class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold bg-fog-white/80 border-t-2 border-border-gray">
                        <td colspan="4" class="px-4 py-3.5 text-right text-caption uppercase tracking-wider text-slate-gray font-bold">
                            Total Tagihan Pesanan
                        </td>
                        <td class="px-4 py-3.5 text-right text-body-sm font-bold text-primary tabular-nums">
                            Rp {{ number_format($this->total, 2, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Form Actions Footer --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-3 border-t border-border-gray/60">
            <button
                type="button"
                wire:click="addItem"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-input text-body-sm font-semibold text-primary bg-primary-tint/60 hover:bg-primary-tint border border-primary/20 transition"
            >
                <x-dynamic-component component="lucide-plus" class="w-4 h-4" />
                <span>Tambah Baris Item</span>
            </button>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <x-button variant="secondary" size="md" href="{{ route('sales.orders.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit" wire:loading.attr="disabled" class="gap-2">
                    <span wire:loading.remove wire:target="save">Buat Sales Order</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <x-dynamic-component component="lucide-loader-2" class="w-4 h-4 animate-spin" />
                        <span>Memproses...</span>
                    </span>
                </x-button>
            </div>
        </div>
    </form>
</div>
