<x-app-layout>
    <x-slot name="header">
        Riwayat Pesanan Penjualan (Sales Orders)
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

    {{-- Orders Table Card --}}
    <x-data-table
        title="Daftar Pesanan Masuk"
        subtitle="Kelola status pemrosesan pesanan pelanggan, reservasi stok gudang, dan penerbitan faktur"
        :headers="['No. Pesanan & Pelanggan', 'Tanggal Order', 'Total Transaksi', 'Status Order', 'Aksi']"
        :empty="$orders->isEmpty()"
    >
        <x-slot name="action">
            @can('sales.order.create')
                <x-button variant="primary" size="sm" href="{{ route('sales.orders.create') }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-plus-circle" class="w-4 h-4" />
                    <span>Buat Pesanan</span>
                </x-button>
            @endcan
        </x-slot>

        <x-slot name="emptyState">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                    <x-dynamic-component component="lucide-shopping-cart" class="w-6 h-6" />
                </div>
                <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Pesanan Penjualan</h4>
                <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Buat Sales Order pertama untuk mulai memproses pesanan dan memotong kuantitas stok.</p>
                @can('sales.order.create')
                    <x-button variant="primary" href="{{ route('sales.orders.create') }}" size="sm">
                        + Buat Sales Order
                    </x-button>
                @endcan
            </div>
        </x-slot>

        @foreach ($orders as $order)
            <tr class="hover:bg-mist-gray/40 transition-colors">
                {{-- No. Order & Pelanggan --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-card bg-primary-tint text-primary font-bold flex items-center justify-center shrink-0 shadow-subtle">
                            <x-dynamic-component component="lucide-shopping-bag" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="font-mono text-body-sm font-bold text-ink-black leading-tight tabular-nums">
                                {{ $order->order_number }}
                            </p>
                            <p class="text-caption font-semibold text-primary mt-0.5">
                                {{ $order->customer?->name ?? 'Pelanggan Umum' }}
                            </p>
                        </div>
                    </div>
                </td>

                {{-- Tanggal Order --}}
                <td class="px-6 py-4 text-body-sm text-slate-gray font-medium tabular-nums">
                    {{ $order->order_date ? $order->order_date->format('d M Y') : '-' }}
                </td>

                {{-- Total Amount --}}
                <td class="px-6 py-4 text-body-sm font-bold text-ink-black tabular-nums">
                    Rp {{ number_format($order->total_amount, 2, ',', '.') }}
                </td>

                {{-- Status --}}
                <td class="px-6 py-4">
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
                </td>

                {{-- Aksi --}}
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end">
                        <a href="{{ route('sales.orders.show', $order) }}"
                           class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                           title="Lihat Detail Pesanan">
                            <x-dynamic-component component="lucide-eye" class="w-4 h-4" />
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $orders->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
