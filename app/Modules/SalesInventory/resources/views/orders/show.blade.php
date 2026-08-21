<x-app-layout>
    <x-slot name="header">
        Detail Pesanan: {{ $order->order_number }}
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

    @if ($order->status === 'cancelled')
        <div class="mb-5 rounded-input bg-danger-bg border border-danger/20 text-danger p-4 text-body-sm flex items-start gap-3">
            <x-dynamic-component component="lucide-alert-triangle" class="w-5 h-5 shrink-0 mt-0.5" />
            <div>
                <h4 class="font-bold text-danger">Pesanan Ini Telah Dibatalkan</h4>
                <p class="text-caption mt-0.5"><strong class="font-semibold">Alasan:</strong> {{ $order->cancel_reason ?: 'Tidak ada alasan pembatalan yang disertakan.' }}</p>
            </div>
        </div>
    @endif

    <div class="w-full space-y-6">
        {{-- Summary Header Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-card bg-primary-tint text-primary flex items-center justify-center shrink-0 shadow-subtle">
                    <x-dynamic-component component="lucide-shopping-bag" class="w-6 h-6" />
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="font-mono text-heading-sm font-bold text-ink-black tracking-tight tabular-nums">
                            {{ $order->order_number }}
                        </h2>
                        <x-badge
                            :status="match($order->status) {
                                'completed', 'invoiced' => 'success',
                                'cancelled'             => 'danger',
                                'confirmed'             => 'primary',
                                default                 => 'warning',
                            }"
                            variant="solid"
                        >
                            {{ ucfirst($order->status) }}
                        </x-badge>
                    </div>
                    <p class="text-caption text-slate-gray mt-1 flex flex-wrap items-center gap-2 font-medium">
                        <span>Pelanggan: <strong class="text-ink-black">{{ $order->customer?->name ?? '-' }}</strong></span>
                        <span>•</span>
                        <span>Tanggal: <strong class="text-ink-black">{{ $order->order_date ? $order->order_date->format('d M Y') : '-' }}</strong></span>
                        @if ($order->invoice)
                            <span>•</span>
                            <span>Faktur:
                                <a href="{{ route('finance.invoices.show', $order->invoice) }}" class="text-primary hover:underline font-semibold font-mono">
                                    {{ $order->invoice->invoice_number }}
                                </a>
                            </span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2.5 self-end md:self-auto">
                <x-button variant="secondary" size="sm" href="{{ route('sales.orders.index') }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-arrow-left" class="w-4 h-4" />
                    <span>Kembali</span>
                </x-button>

                @can('sales.order.complete', $order)
                    @if ($order->status !== 'completed' && $order->status !== 'cancelled')
                        <form method="POST" action="{{ route('sales.orders.complete', $order) }}" class="inline">
                            @csrf
                            <x-button type="submit" variant="primary" size="sm" class="gap-1.5">
                                <x-dynamic-component component="lucide-check-check" class="w-4 h-4" />
                                <span>Complete Order</span>
                            </x-button>
                        </form>
                    @endif
                @endcan

                @can('sales.order.cancel', $order)
                    @if ($order->isCancellable())
                        <x-button variant="danger" size="sm" type="button" x-data="" @click="$dispatch('open-modal', 'cancel-order-modal')" class="gap-1.5">
                            <x-dynamic-component component="lucide-x-circle" class="w-4 h-4" />
                            <span>Batalkan Order</span>
                        </x-button>
                    @endif
                @endcan
            </div>
        </div>

        {{-- Order Items Table Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card shadow-subtle overflow-hidden">
            <div class="px-6 py-4 border-b border-border-gray/60 flex items-center justify-between">
                <h3 class="text-heading-sm font-semibold text-ink-black tracking-tight">Rincian Barang / Jasa Dipesan</h3>
                <span class="text-caption font-semibold text-slate-gray tabular-nums">
                    Total {{ $order->items->count() }} Baris Item
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-gray/60 bg-fog-white/60">
                            <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider">Item / Layanan</th>
                            <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider text-right">Kuantitas (Qty)</th>
                            <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider text-right">Harga Satuan</th>
                            <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-gray/50">
                        @foreach ($order->items as $line)
                            <tr class="hover:bg-mist-gray/40 transition-colors">
                                <td class="px-6 py-4 text-body-sm font-medium text-ink-black">
                                    <div class="flex items-center gap-2.5">
                                        <span class="font-mono text-caption text-primary bg-primary-tint/60 px-2 py-0.5 rounded-input border border-primary/20">
                                            {{ $line->item->sku }}
                                        </span>
                                        <span>{{ $line->item->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-body-sm font-medium text-ink-black text-right tabular-nums">
                                    {{ $line->quantity }} {{ $line->item->unit_of_measure }}
                                </td>
                                <td class="px-6 py-4 text-body-sm font-medium text-slate-gray text-right tabular-nums">
                                    Rp {{ number_format($line->unit_price, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-body-sm font-bold text-ink-black text-right tabular-nums">
                                    Rp {{ number_format($line->subtotal, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold bg-fog-white/80 border-t-2 border-border-gray">
                            <td colspan="3" class="px-6 py-4 text-right text-caption uppercase tracking-wider text-slate-gray font-bold">
                                Total Keseluruhan Pesanan
                            </td>
                            <td class="px-6 py-4 text-right text-heading-sm font-bold text-primary tabular-nums">
                                Rp {{ number_format($order->total_amount, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Alpine Modal Pembatalan Sales Order --}}
    <x-modal name="cancel-order-modal" maxWidth="md">
        @php
            $hasReasonError = $errors->has('reason');
        @endphp

        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-danger-bg text-danger flex items-center justify-center shrink-0">
                <x-dynamic-component component="lucide-alert-triangle" class="w-5 h-5" />
            </div>
            <div>
                <h3 class="text-heading-sm font-semibold text-ink-black">Batalkan Sales Order?</h3>
                <p class="text-caption text-slate-gray mt-0.5">Stok reservasi gudang akan otomatis dikembalikan.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('sales.orders.cancel', $order) }}" class="space-y-4">
            @csrf

            <div>
                <label for="reason" class="block text-label font-medium text-slate-gray mb-1.5">Alasan Pembatalan <span class="text-danger">*</span></label>
                <textarea
                    name="reason"
                    id="reason"
                    required
                    rows="3"
                    placeholder="Contoh: Permintaan pembatalan dari klien karena perubahan spesifikasi"
                    class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body-sm text-ink-black placeholder-ash-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $hasReasonError ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                >{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-gray/60">
                <x-button type="button" variant="secondary" size="sm" x-on:click="$dispatch('close-modal', 'cancel-order-modal')">
                    Batal
                </x-button>
                <x-button type="submit" variant="danger" size="sm" class="bg-danger text-paper-white hover:opacity-90">
                    Ya, Batalkan Order
                </x-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
