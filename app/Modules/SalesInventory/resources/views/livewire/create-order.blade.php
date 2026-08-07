<form wire:submit="save" class="space-y-6">
    <div>
        <label class="text-label text-slate-gray block mb-1">Customer</label>
        <select wire:model="customer_id" class="w-full rounded-input border-border-gray">
            <option value="">-- Pilih Customer --</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
            @endforeach
        </select>
        @error('customer_id') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="text-label text-slate-gray block mb-1">Tanggal Order</label>
        <input type="date" wire:model="order_date" class="rounded-input border-border-gray" />
    </div>

    @error('items') <p class="text-caption text-danger">{{ $message }}</p> @enderror

    <x-data-table
        :headers="['Item', 'Qty', 'Stok Tersedia', 'Harga Satuan', 'Subtotal', '']"
        :empty="empty($this->items)"
    >
        @foreach ($this->items as $key => $line)
            <tr wire:key="order-item-{{ $key }}">
                <td class="px-4 py-2">
                    <select wire:model.live="items.{{ $key }}.item_id" wire:change="itemSelected({{ $key }})"
                        class="w-full rounded-input border-border-gray">
                        <option value="">-- Pilih Item --</option>
                        @foreach ($availableItems as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->item_type === 'physical_good' ? 'Barang' : 'Jasa' }} - {{ $item->name }} ({{ $item->sku }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="px-4 py-2">
                    <input type="number" min="1" step="1"
                        @if(!is_null($line['available_stock'] ?? null)) max="{{ (int) floor($line['available_stock']) }}" @endif
                        wire:model.live="items.{{ $key }}.quantity"
                        class="w-full rounded-input border-border-gray text-right tabular-nums" />
                </td>
                <td class="px-4 py-2 text-right tabular-nums text-slate-gray">
                    {{ is_null($line['available_stock'] ?? null) ? '-' : (int) floor($line['available_stock']) }}
                </td>
                <td class="px-4 py-2">
                    <input type="number" readonly value="{{ number_format($line['unit_price'], 0, ',', '.') }}"
                        class="w-full rounded-input border-border-gray bg-gray-100 text-right" />
                </td>
                <td class="px-4 py-2 text-right tabular-nums">
                    Rp {{ number_format($this->getSubtotal($line), 0, ',', '.') }}
                </td>
                <td class="px-4 py-2 text-center">
                    <button type="button" wire:click="removeItem({{ $key }})" class="text-danger hover:opacity-70">
                        <x-lucide-trash-2 class="w-4 h-4" />
                    </button>
                </td>
            </tr>
        @endforeach
    </x-data-table>

    <button type="button" wire:click="addItem" class="text-body-sm text-info">
        + Tambah Baris Item
    </button>

    <div class="flex justify-end">
        <div class="text-body-lg font-medium">
            Total: Rp {{ number_format($this->total, 0, ',', '.') }}
        </div>
    </div>

    <div class="flex gap-2">
        <x-button type="submit" variant="primary">Buat Sales Order</x-button>
        <x-button variant="danger" href="{{ route('sales.orders.index') }}">Batal</x-button>
    </div>
</form>
