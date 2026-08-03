<div>
    <div class="rounded-card border border-border-gray bg-paper-white p-6 shadow-subtle">
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-label text-slate-gray mb-1">Tanggal Entry</label>
                <input type="date" wire:model="entry_date" class="w-full rounded-input border border-border-gray px-3 py-2 text-body focus:border-ink-black focus:shadow-focus-ring outline-none">
                @error('entry_date') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-label text-slate-gray mb-1">Deskripsi</label>
                <input type="text" wire:model="description" class="w-full rounded-input border border-border-gray px-3 py-2 text-body focus:border-ink-black focus:shadow-focus-ring outline-none">
            </div>
        </div>

        <table class="w-full text-body">
            <thead class="bg-fog-white">
                <tr class="text-label text-slate-gray">
                    <th class="text-left px-4 py-3">Akun</th>
                    <th class="text-left px-4 py-3">Deskripsi</th>
                    <th class="text-right px-4 py-3">Debit</th>
                    <th class="text-right px-4 py-3">Credit</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lines as $key => $line)
                    <tr wire:key="line-{{ $key }}" class="border-b border-border-gray">
                        <td class="px-4 py-3">
                            <select wire:model="lines.{{ $key }}.account_id" class="w-full rounded-input border border-border-gray px-2 py-1.5">
                                <option value="">-- pilih akun --</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                            @error("lines.{$key}.account_id") <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
                        </td>
                        <td class="px-4 py-3">
                            <input type="text" wire:model="lines.{{ $key }}.description" class="w-full rounded-input border border-border-gray px-2 py-1.5">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" step="0.01" min="0" wire:model.live.debounce.300ms="lines.{{ $key }}.debit" class="w-full text-right tabular-nums rounded-input border border-border-gray px-2 py-1.5">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" step="0.01" min="0" wire:model.live.debounce.300ms="lines.{{ $key }}.credit" class="w-full text-right tabular-nums rounded-input border border-border-gray px-2 py-1.5">
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button type="button" wire:click="removeLine({{ $key }})" class="text-danger text-body-sm">Hapus</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-medium">
                    <td colspan="2" class="px-4 py-3 text-right text-label text-slate-gray">Total</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ $this->totalDebit }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ $this->totalCredit }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        @error('lines') <p class="text-caption text-danger mt-2">{{ $message }}</p> @enderror

        <div class="flex items-center justify-between mt-4">
            <button type="button" wire:click="addLine" class="text-body-sm text-info">+ Tambah Baris</button>

            <div class="flex gap-3">
                <x-button variant="secondary" href="{{ route('finance.journal-entries.index') }}">Batal</x-button>
                <x-button variant="primary" wire:click="save" wire:loading.attr="disabled">Simpan Journal Entry</x-button>
            </div>
        </div>
    </div>
</div>
