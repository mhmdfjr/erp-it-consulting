<x-app-layout>
    <x-slot name="header">
        Daftar Faktur Penjualan (Invoices)
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

    {{-- Invoices Table Card --}}
    <x-data-table
        title="Daftar Faktur Penjualan (Accounts Receivable)"
        subtitle="Kelola tagihan pelanggan, batas jatuh tempo termin pembayaran, dan riwayat penerimaan kas"
        :headers="['No. Faktur & Customer', 'Tgl. Terbit', 'Jatuh Tempo', 'Total Tagihan', 'Status', 'Aksi']"
        :empty="$invoices->isEmpty()"
    >
        <x-slot name="emptyState">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                    <x-dynamic-component component="lucide-file-spreadsheet" class="w-6 h-6" />
                </div>
                <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Faktur Penjualan</h4>
                <p class="text-caption text-slate-gray mt-1 max-w-sm">
                    Faktur terbit secara otomatis setelah pesanan penjualan (Sales Order) dikonfirmasi selesai.
                </p>
            </div>
        </x-slot>

        @foreach ($invoices as $invoice)
            <tr class="hover:bg-mist-gray/40 transition-colors">
                {{-- No Invoice & Customer Name --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-card bg-primary-tint text-primary font-bold flex items-center justify-center shrink-0 shadow-subtle">
                            <x-dynamic-component component="lucide-file-text" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="font-mono text-body-sm font-bold text-ink-black leading-tight tabular-nums">
                                {{ $invoice->invoice_number }}
                            </p>
                            <p class="text-caption font-semibold text-primary mt-0.5">
                                {{ $invoice->salesOrder?->customer?->name ?? 'Customer Umum' }}
                            </p>
                        </div>
                    </div>
                </td>

                {{-- Tanggal Invoice --}}
                <td class="px-6 py-4 text-body-sm text-slate-gray font-medium tabular-nums">
                    {{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : '-' }}
                </td>

                {{-- Jatuh Tempo --}}
                <td class="px-6 py-4">
                    @php
                        $isOverdue = $invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'paid';
                    @endphp
                    <div class="flex items-center gap-1.5 text-body-sm tabular-nums font-semibold {{ $isOverdue ? 'text-danger' : 'text-slate-gray' }}">
                        <x-dynamic-component component="lucide-calendar" class="w-3.5 h-3.5 text-ash-gray" />
                        <span>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</span>
                    </div>
                </td>

                {{-- Total Tagihan --}}
                <td class="px-6 py-4 text-body-sm font-bold text-ink-black tabular-nums">
                    Rp {{ number_format($invoice->amount, 2, ',', '.') }}
                </td>

                {{-- Status --}}
                <td class="px-6 py-4">
                    <x-badge
                        :status="match (true) {
                            $invoice->status === 'paid' => 'success',
                            $isOverdue => 'danger',
                            $invoice->status === 'draft' => 'info',
                            default => 'warning'
                        }"
                        variant="solid"
                    >
                        {{ $isOverdue ? 'Overdue' : ucfirst($invoice->status) }}
                    </x-badge>
                </td>

                {{-- Aksi --}}
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end">
                        <a href="{{ route('finance.invoices.show', $invoice) }}"
                           class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                           title="Lihat Detail Faktur">
                            <x-dynamic-component component="lucide-eye" class="w-4 h-4" />
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $invoices->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
