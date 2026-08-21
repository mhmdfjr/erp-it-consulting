{{-- resources/views/livewire/finance/create-journal-entry.blade.php --}}
<div class="space-y-6">
    <div class="rounded-card border border-border-gray/80 bg-paper-white p-6 shadow-subtle">
        {{-- Form Header --}}
        <div class="border-b border-border-gray/60 pb-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-heading-sm font-semibold text-ink-black tracking-tight">Formulir Jurnal Manual</h3>
                <p class="text-caption text-slate-gray mt-0.5">Catat mutasi debit dan kredit dengan keseimbangan neraca saldo (balance)</p>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $isBalanced = abs((float)$this->totalDebit - (float)$this->totalCredit) < 0.001 && (float)$this->totalDebit > 0;
                @endphp
                <x-badge :status="$isBalanced ? 'success' : 'warning'" variant="solid">
                    {{ $isBalanced ? 'Status: Balanced' : 'Status: Unbalanced' }}
                </x-badge>
            </div>
        </div>

        {{-- General Information Fields --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            <div>
                <label class="block text-label font-medium text-slate-gray mb-1.5">Tanggal Entri <span class="text-danger">*</span></label>
                <input
                    type="date"
                    wire:model="entry_date"
                    class="w-full rounded-input border bg-paper-white px-3.5 py-2 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('entry_date') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                >
                @error('entry_date')
                    <p class="text-caption font-medium text-danger mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-label font-medium text-slate-gray mb-1.5">Deskripsi / Memo Transaksi</label>
                <input
                    type="text"
                    wire:model="description"
                    placeholder="Contoh: Pembayaran beban operasional kantor"
                    class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2 text-body-sm text-ink-black placeholder-ash-gray transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                >
                @error('description')
                    <p class="text-caption font-medium text-danger mt-1.5">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Interactive Lines Table --}}
        <div class="overflow-x-auto border border-border-gray/80 rounded-card mb-4 shadow-subtle">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border-gray/60 bg-fog-white/60">
                        <th scope="col" class="px-4 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider min-w-[240px]">Akun COA <span class="text-danger">*</span></th>
                        <th scope="col" class="px-4 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider min-w-[200px]">Deskripsi Baris</th>
                        <th scope="col" class="px-4 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider text-right w-[160px]">Debit (Rp)</th>
                        <th scope="col" class="px-4 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider text-right w-[160px]">Kredit (Rp)</th>
                        <th scope="col" class="px-3 py-3.5 text-center w-[50px]"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-gray/50">
                    @foreach ($lines as $key => $line)
                        <tr wire:key="line-{{ $key }}" class="hover:bg-mist-gray/30 transition-colors">
                            {{-- Account Select --}}
                            <td class="p-3">
                                <select
                                    wire:model="lines.{{ $key }}.account_id"
                                    class="w-full rounded-input border bg-paper-white px-3 py-1.5 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has("lines.{$key}.account_id") ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                                >
                                    <option value="">-- Pilih Akun COA --</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->code }} - {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("lines.{$key}.account_id")
                                    <p class="text-caption font-medium text-danger mt-1">{{ $message }}</p>
                                @enderror
                            </td>

                            {{-- Line Description --}}
                            <td class="p-3">
                                <input
                                    type="text"
                                    wire:model="lines.{{ $key }}.description"
                                    placeholder="Catatan baris"
                                    class="w-full rounded-input border border-border-gray bg-paper-white px-3 py-1.5 text-body-sm text-ink-black placeholder-ash-gray transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                                >
                            </td>

                            {{-- Debit --}}
                            <td class="p-3">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    wire:model.live.debounce.300ms="lines.{{ $key }}.debit"
                                    placeholder="0.00"
                                    class="w-full text-right font-medium tabular-nums rounded-input border border-border-gray bg-paper-white px-3 py-1.5 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                                >
                            </td>

                            {{-- Credit --}}
                            <td class="p-3">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    wire:model.live.debounce.300ms="lines.{{ $key }}.credit"
                                    placeholder="0.00"
                                    class="w-full text-right font-medium tabular-nums rounded-input border border-border-gray bg-paper-white px-3 py-1.5 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                                >
                            </td>

                            {{-- Remove Button --}}
                            <td class="p-3 text-center">
                                <button
                                    type="button"
                                    wire:click="removeLine({{ $key }})"
                                    class="p-1.5 rounded-input text-danger hover:bg-danger-bg transition"
                                    title="Hapus Baris"
                                    @if(count($lines) <= 2) disabled @endif
                                >
                                    <x-dynamic-component component="lucide-trash-2" class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold bg-fog-white/80 border-t-2 border-border-gray">
                        <td colspan="2" class="px-4 py-3.5 text-right text-caption uppercase tracking-wider text-slate-gray font-bold">
                            Total Saldo
                        </td>
                        <td class="px-4 py-3.5 text-right text-body-sm font-bold text-ink-black tabular-nums">
                            {{ is_numeric($this->totalDebit) ? 'Rp ' . number_format((float)$this->totalDebit, 2, ',', '.') : $this->totalDebit }}
                        </td>
                        <td class="px-4 py-3.5 text-right text-body-sm font-bold text-ink-black tabular-nums">
                            {{ is_numeric($this->totalCredit) ? 'Rp ' . number_format((float)$this->totalCredit, 2, ',', '.') : $this->totalCredit }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @error('lines')
            <div class="mb-4 rounded-input bg-danger-bg border border-danger/20 text-danger px-4 py-2.5 text-caption font-medium flex items-center gap-2">
                <x-dynamic-component component="lucide-alert-circle" class="w-4 h-4 shrink-0" />
                <span>{{ $message }}</span>
            </div>
        @enderror

        {{-- Form Actions Footer --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-3 border-t border-border-gray/60">
            <button
                type="button"
                wire:click="addLine"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-input text-body-sm font-semibold text-primary bg-primary-tint/60 hover:bg-primary-tint border border-primary/20 transition"
            >
                <x-dynamic-component component="lucide-plus" class="w-4 h-4" />
                <span>Tambah Baris Akun</span>
            </button>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <x-button variant="secondary" size="md" href="{{ route('finance.journal-entries.index') }}">
                    Batal
                </x-button>

                <x-button
                    variant="primary"
                    size="md"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="gap-2"
                >
                    <span wire:loading.remove wire:target="save">Simpan Jurnal Entry</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <x-dynamic-component component="lucide-loader-2" class="w-4 h-4 animate-spin" />
                        <span>Menyimpan...</span>
                    </span>
                </x-button>
            </div>
        </div>
    </div>
</div>
