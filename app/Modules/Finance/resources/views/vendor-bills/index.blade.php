<x-app-layout>
    <x-slot name="header">
        Daftar Tagihan Vendor (Vendor Bills)
    </x-slot>

    {{-- Alert Notifications --}}
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

    {{-- Vendor Bills Table Card --}}
    <x-data-table
        title="Daftar Tagihan Pembelian"
        subtitle="Kelola faktur tagihan masuk dari vendor rekanan, termin pembayaran, dan status pelunasan"
        :headers="['No. Tagihan & Vendor', 'Tgl. Terbit', 'Jatuh Tempo', 'Total Tagihan', 'Status', 'Aksi']"
        :empty="$bills->isEmpty()"
    >
        <x-slot name="action">
            @can('finance.vendorbill.create')
                <x-button href="{{ route('finance.vendor-bills.create') }}" variant="primary" size="sm" class="gap-1.5">
                    <x-dynamic-component component="lucide-plus-circle" class="w-4 h-4" />
                    <span>Buat Tagihan Vendor</span>
                </x-button>
            @endcan
        </x-slot>

        <x-slot name="emptyState">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                    <x-dynamic-component component="lucide-receipt" class="w-6 h-6" />
                </div>
                <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Tagihan Vendor</h4>
                <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Catat tagihan invoice pengadaan barang/jasa dari vendor untuk menjadwalkan pembayaran utang usaha.</p>
                @can('finance.vendorbill.create')
                    <x-button variant="primary" href="{{ route('finance.vendor-bills.create') }}" size="sm">
                        + Buat Tagihan Vendor
                    </x-button>
                @endcan
            </div>
        </x-slot>

        @foreach ($bills as $bill)
            <tr class="hover:bg-mist-gray/40 transition-colors">
                {{-- No. Bill & Vendor Name --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-card bg-primary-tint text-primary font-bold flex items-center justify-center shrink-0 shadow-subtle">
                            <x-dynamic-component component="lucide-file-text" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="font-mono text-body-sm font-bold text-ink-black leading-tight tabular-nums">
                                {{ $bill->bill_number }}
                            </p>
                            <p class="text-caption font-semibold text-primary mt-0.5">
                                {{ $bill->vendor?->name ?? 'Vendor Tidak Ditemukan' }}
                            </p>
                        </div>
                    </div>
                </td>

                {{-- Tanggal Terbit --}}
                <td class="px-6 py-4 text-body-sm text-slate-gray font-medium tabular-nums">
                    {{ $bill->bill_date ? $bill->bill_date->format('d M Y') : '-' }}
                </td>

                {{-- Jatuh Tempo --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-1.5 text-body-sm tabular-nums font-semibold {{ $bill->due_date && $bill->due_date->isPast() && $bill->status !== 'paid' ? 'text-danger' : 'text-slate-gray' }}">
                        <x-dynamic-component component="lucide-calendar" class="w-3.5 h-3.5 text-ash-gray" />
                        <span>{{ $bill->due_date ? $bill->due_date->format('d M Y') : '-' }}</span>
                    </div>
                </td>

                {{-- Jumlah --}}
                <td class="px-6 py-4 text-body-sm font-bold text-ink-black tabular-nums">
                    Rp {{ number_format($bill->amount, 2, ',', '.') }}
                </td>

                {{-- Status --}}
                <td class="px-6 py-4">
                    <x-badge
                        :status="match ($bill->status) {
                            'paid'     => 'success',
                            'approved' => 'warning',
                            'unpaid'   => 'warning',
                            'void'     => 'danger',
                            default    => 'info'
                        }"
                        variant="solid"
                    >
                        {{ ucfirst($bill->status) }}
                    </x-badge>
                </td>

                {{-- Aksi --}}
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="{{ route('finance.vendor-bills.show', $bill) }}"
                           class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                           title="Lihat Detail Tagihan">
                            <x-dynamic-component component="lucide-eye" class="w-4 h-4" />
                        </a>

                        @can('finance.vendorbill.edit')
                            @if ($bill->status !== 'paid' && $bill->status !== 'void')
                                <a href="{{ route('finance.vendor-bills.edit', $bill) }}"
                                   class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                                   title="Edit Tagihan">
                                    <x-dynamic-component component="lucide-pencil" class="w-4 h-4" />
                                </a>
                            @endif
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $bills->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
