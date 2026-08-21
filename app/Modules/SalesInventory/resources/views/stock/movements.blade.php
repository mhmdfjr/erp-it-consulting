<x-app-layout>
    <x-slot name="header">
        Riwayat Mutasi Stok: {{ $item->name }}
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

    @php
        $onHand = $item->stockLevel->quantity_on_hand ?? 0;
        $reserved = $item->stockLevel->quantity_reserved ?? 0;
        $available = max(0, $onHand - $reserved);
    @endphp

    <div class="space-y-6">
        {{-- Item Header & Metric Summary Card --}}
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
                    <p class="text-caption text-slate-gray mt-1 flex items-center gap-2 font-medium">
                        <span>Satuan: <strong class="text-ink-black uppercase">{{ $item->unit_of_measure }}</strong></span>
                        <span>•</span>
                        <span>Kategori: <strong class="text-ink-black">{{ $item->category?->name ?? 'Umum' }}</strong></span>
                    </p>
                </div>
            </div>

            {{-- Metric Badges --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-3 bg-fog-white border border-border-gray/80 rounded-card p-3">
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
                        <span class="text-[10px] font-bold text-ash-gray uppercase tracking-wider block">Siap Dijual</span>
                        <span class="text-body-sm font-bold text-success tabular-nums">{{ (int) floor($available) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Movements Table Card --}}
        <x-data-table
            title="Log Riwayat Mutasi Stok"
            subtitle="Catatan kronologis penerimaan, pengurangan penjualan, dan penyesuaian fisik inventaris"
            :headers="['Waktu & Tanggal', 'Tipe Pergerakan', 'Kuantitas Mutasi', 'Alasan / Referensi']"
            :empty="$movements->isEmpty()"
        >
            <x-slot name="action">
                <div class="flex items-center gap-2.5">
                    <x-button variant="secondary" size="sm" href="{{ route('sales.items.edit', $item) }}" class="gap-1.5">
                        <x-dynamic-component component="lucide-arrow-left" class="w-4 h-4" />
                        <span>Kembali ke Item</span>
                    </x-button>

                    <x-button variant="primary" size="sm" href="{{ route('sales.stock.adjust', $item) }}" class="gap-1.5">
                        <x-dynamic-component component="lucide-plus" class="w-4 h-4" />
                        <span>Penyesuaian Stok</span>
                    </x-button>
                </div>
            </x-slot>

            <x-slot name="emptyState">
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                        <x-dynamic-component component="lucide-history" class="w-6 h-6" />
                    </div>
                    <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Mutasi Stok</h4>
                    <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Lakukan penyesuaian stok manual atau selesaikan Sales Order untuk melihat mutasi barang.</p>
                    <x-button variant="primary" href="{{ route('sales.stock.adjust', $item) }}" size="sm">
                        + Penyesuaian Stok Pertama
                    </x-button>
                </div>
            </x-slot>

            @foreach ($movements as $movement)
                @php
                    $isPositive = str_contains(strtolower($movement->movement_type), 'in') || (float)$movement->quantity > 0;
                @endphp
                <tr class="hover:bg-mist-gray/40 transition-colors">
                    {{-- Waktu & Tanggal --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <x-dynamic-component component="lucide-clock" class="w-3.5 h-3.5 text-ash-gray" />
                            <span class="text-body-sm font-medium text-ink-black tabular-nums">
                                {{ $movement->created_at ? $movement->created_at->format('d M Y, H:i') : '-' }}
                            </span>
                        </div>
                    </td>

                    {{-- Tipe Pergerakan --}}
                    <td class="px-6 py-4">
                        <x-badge
                            :status="$isPositive ? 'success' : 'warning'"
                            variant="subtle"
                        >
                            {{ strtoupper($movement->movement_type) }}
                        </x-badge>
                    </td>

                    {{-- Kuantitas Mutasi --}}
                    <td class="px-6 py-4">
                        <span class="font-mono text-body-sm font-bold tabular-nums {{ $isPositive ? 'text-success' : 'text-danger' }}">
                            {{ $isPositive ? '+' : '' }}{{ (float) $movement->quantity }} {{ $item->unit_of_measure }}
                        </span>
                    </td>

                    {{-- Alasan / Referensi Dokumen --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-caption font-semibold text-slate-gray bg-fog-white border border-border-gray/80 px-2.5 py-0.5 rounded-input">
                                <x-dynamic-component component="lucide-file-text" class="w-3 h-3 text-ash-gray" />
                                {{ $movement->reason_code ?: ($movement->reference_type ? class_basename($movement->reference_type) : 'Manual Adjustment') }}
                            </span>
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot name="pagination">
                {{ $movements->links() }}
            </x-slot>
        </x-data-table>
    </div>
</x-app-layout>
