<x-app-layout>
    <x-slot name="header">
        Entri Jurnal Umum
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

    {{-- Journal Table Card --}}
    <x-data-table
        title="Daftar Entri Jurnal"
        subtitle="Riwayat pencatatan transaksi pembukuan debit dan kredit dalam sistem keuangan"
        :headers="['No. Entri & Tanggal', 'Deskripsi / Memo', 'Tipe Referensi', 'Status', 'Aksi']"
        :empty="$entries->isEmpty()"
    >
        <x-slot name="action">
            @can('finance.journal.create')
                <x-button href="{{ route('finance.journal-entries.create') }}" variant="primary" size="sm" class="gap-1.5">
                    <x-dynamic-component component="lucide-plus-circle" class="w-4 h-4" />
                    <span>Buat Entri Jurnal</span>
                </x-button>
            @endcan
        </x-slot>

        <x-slot name="emptyState">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                    <x-dynamic-component component="lucide-book-open-text" class="w-6 h-6" />
                </div>
                <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Entri Jurnal</h4>
                <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Buat pencatatan jurnal pertama untuk mendokumentasikan mutasi transaksi akuntansi.</p>
                @can('finance.journal.create')
                    <x-button variant="primary" href="{{ route('finance.journal-entries.create') }}" size="sm">
                        + Buat Entri Jurnal
                    </x-button>
                @endcan
            </div>
        </x-slot>

        @foreach ($entries as $entry)
            <tr class="hover:bg-mist-gray/40 transition-colors">
                {{-- No Entry & Tanggal --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-card bg-primary-tint text-primary font-bold flex items-center justify-center shrink-0 shadow-subtle">
                            <x-dynamic-component component="lucide-receipt" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="font-mono text-body-sm font-bold text-ink-black leading-tight tabular-nums">
                                {{ $entry->entry_number }}
                            </p>
                            <p class="text-caption text-slate-gray font-medium tabular-nums mt-0.5">
                                {{ $entry->entry_date ? $entry->entry_date->format('d M Y') : '-' }}
                            </p>
                        </div>
                    </div>
                </td>

                {{-- Deskripsi / Keterangan --}}
                <td class="px-6 py-4">
                    <p class="text-body-sm text-ink-black font-medium leading-snug line-clamp-1">
                        {{ $entry->description ?: 'Tanpa keterangan transaksi' }}
                    </p>
                </td>

                {{-- Tipe Referensi --}}
                <td class="px-6 py-4">
                    @if ($entry->reference_type)
                        <span class="inline-flex items-center gap-1.5 text-caption font-semibold text-slate-gray bg-fog-white border border-border-gray/80 px-2.5 py-1 rounded-input">
                            <x-dynamic-component component="lucide-link" class="w-3 h-3 text-primary" />
                            {{ class_basename($entry->reference_type) }} #{{ $entry->reference_id }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-caption font-semibold text-slate-gray bg-fog-white border border-border-gray/80 px-2.5 py-1 rounded-input">
                            <x-dynamic-component component="lucide-edit-3" class="w-3 h-3 text-ash-gray" />
                            Manual
                        </span>
                    @endif
                </td>

                {{-- Status --}}
                <td class="px-6 py-4">
                    <x-badge
                        :status="match ($entry->status) {
                            'posted' => 'success',
                            'void'   => 'danger',
                            default  => 'warning'
                        }"
                        variant="solid"
                    >
                        {{ ucfirst($entry->status) }}
                    </x-badge>
                </td>

                {{-- Aksi --}}
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end">
                        <a href="{{ route('finance.journal-entries.show', $entry) }}"
                           class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                           title="Lihat Detail Entri">
                            <x-dynamic-component component="lucide-eye" class="w-4 h-4" />
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $entries->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
