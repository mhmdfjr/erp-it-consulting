<x-app-layout>
    <x-slot name="header">
        Detail Jurnal: {{ $journalEntry->entry_number }}
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

    @if ($journalEntry->status === 'void')
        <div class="mb-5 rounded-input bg-danger-bg border border-danger/20 text-danger p-4 text-body-sm flex items-start gap-3">
            <x-dynamic-component component="lucide-alert-triangle" class="w-5 h-5 shrink-0 mt-0.5" />
            <div>
                <h4 class="font-bold text-danger">Entri Jurnal Ini Dibatalkan (Void)</h4>
                <p class="text-caption mt-0.5"><strong class="font-semibold">Alasan pembatalan:</strong> {{ $journalEntry->void_reason ?: 'Tidak ada keterangan alasan.' }}</p>
            </div>
        </div>
    @endif

    <div class="space-y-6">
        {{-- Header Summary Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-card bg-primary-tint text-primary flex items-center justify-center shrink-0 shadow-subtle">
                    <x-dynamic-component component="lucide-receipt" class="w-6 h-6" />
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="font-mono text-heading-sm font-bold text-ink-black tracking-tight tabular-nums">
                            {{ $journalEntry->entry_number }}
                        </h2>
                        <x-badge
                            :status="match ($journalEntry->status) {
                                'posted' => 'success',
                                'void'   => 'danger',
                                default  => 'warning'
                            }"
                            variant="solid"
                        >
                            {{ ucfirst($journalEntry->status) }}
                        </x-badge>
                    </div>
                    <p class="text-caption text-slate-gray mt-1 flex items-center gap-2 font-medium">
                        <span>Tanggal: <strong class="text-ink-black">{{ $journalEntry->entry_date ? $journalEntry->entry_date->format('d M Y') : '-' }}</strong></span>
                        <span>•</span>
                        <span>Referensi: <strong class="text-ink-black">{{ $journalEntry->reference_type ? class_basename($journalEntry->reference_type) . ' #' . $journalEntry->reference_id : 'Manual' }}</strong></span>
                    </p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2.5 self-end md:self-auto">
                <x-button variant="secondary" size="sm" href="{{ route('finance.journal-entries.index') }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-arrow-left" class="w-4 h-4" />
                    <span>Kembali</span>
                </x-button>

                @can('finance.journal.void')
                    @if ($journalEntry->status === 'posted')
                        <x-button variant="danger" size="sm" x-data="" @click="$dispatch('open-modal', 'void-journal-modal')" class="gap-1.5">
                            <x-dynamic-component component="lucide-x-circle" class="w-4 h-4" />
                            <span>Void Entry</span>
                        </x-button>
                    @endif
                @endcan
            </div>
        </div>

        {{-- Description Memo if Available --}}
        @if ($journalEntry->description)
            <div class="bg-fog-white border border-border-gray/80 rounded-card p-4 text-body-sm text-slate-gray">
                <span class="text-[11px] font-bold text-ash-gray uppercase tracking-wider block mb-1">Catatan / Memo Transaksi</span>
                <p class="text-ink-black font-medium leading-relaxed">{{ $journalEntry->description }}</p>
            </div>
        @endif

        {{-- Journal Lines Table Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card shadow-subtle overflow-hidden">
            <div class="px-6 py-4 border-b border-border-gray/60 flex items-center justify-between">
                <h3 class="text-heading-sm font-semibold text-ink-black tracking-tight">Rincian Pos Debit & Kredit</h3>
                <span class="text-caption font-semibold text-slate-gray tabular-nums">
                    Total {{ $journalEntry->lines->count() }} Baris
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-gray/60 bg-fog-white/60">
                            <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider">Akun Bagan (COA)</th>
                            <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider">Deskripsi Baris</th>
                            <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider text-right">Debit</th>
                            <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider text-right">Kredit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-gray/50">
                        @foreach ($journalEntry->lines as $line)
                            <tr class="hover:bg-mist-gray/40 transition-colors">
                                <td class="px-6 py-4 text-body-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-primary bg-primary-tint/60 px-2 py-0.5 rounded-input text-caption border border-primary/20 tabular-nums">
                                            {{ $line->account->code }}
                                        </span>
                                        <span class="font-medium text-ink-black">{{ $line->account->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-body-sm text-slate-gray font-medium">
                                    {{ $line->description ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-body-sm text-right font-semibold text-ink-black tabular-nums">
                                    {{ $line->debit > 0 ? 'Rp ' . number_format($line->debit, 2, ',', '.') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-body-sm text-right font-semibold text-ink-black tabular-nums">
                                    {{ $line->credit > 0 ? 'Rp ' . number_format($line->credit, 2, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php
                            $totalDebit = $journalEntry->lines->sum('debit');
                            $totalCredit = $journalEntry->lines->sum('credit');
                            $isBalanced = abs($totalDebit - $totalCredit) < 0.001;
                        @endphp
                        <tr class="font-semibold bg-fog-white/80 border-t-2 border-border-gray">
                            <td colspan="2" class="px-6 py-4 text-right text-caption uppercase tracking-wider text-slate-gray">
                                Total Neraca Saldo
                            </td>
                            <td class="px-6 py-4 text-right text-body-sm font-bold text-ink-black tabular-nums">
                                Rp {{ number_format($totalDebit, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-body-sm font-bold text-ink-black tabular-nums">
                                Rp {{ number_format($totalCredit, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Alpine Modal Konfirmasi Void Entry --}}
    <x-modal name="void-journal-modal" maxWidth="md">
        @php
            $hasReasonError = $errors->has('void_reason');
        @endphp

        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-danger-bg text-danger flex items-center justify-center shrink-0">
                <x-dynamic-component component="lucide-alert-triangle" class="w-5 h-5" />
            </div>
            <div>
                <h3 class="text-heading-sm font-semibold text-ink-black">Batalkan Entri Jurnal?</h3>
                <p class="text-caption text-slate-gray mt-0.5">Tindakan ini akan membalikkan mutasi saldo pembukuan.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('finance.journal-entries.void', $journalEntry) }}" class="space-y-4">
            @csrf

            <div>
                <label for="void_reason" class="block text-label font-medium text-slate-gray mb-1.5">Alasan Pembatalan (Void Reason)</label>
                <textarea
                    name="void_reason"
                    id="void_reason"
                    rows="3"
                    required
                    minlength="5"
                    placeholder="Contoh: Kesalahan penginputan nominal faktur"
                    class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body-sm text-ink-black placeholder-ash-gray transition-colors focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $hasReasonError ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                >{{ old('void_reason') }}</textarea>
                @error('void_reason')
                    <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-gray/60">
                <x-button variant="secondary" size="sm" type="button" x-on:click="$dispatch('close-modal', 'void-journal-modal')">
                    Batal
                </x-button>

                <x-button variant="danger" size="sm" type="submit">
                    Ya, Batalkan Entri
                </x-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
