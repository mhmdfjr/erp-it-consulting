<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-heading font-medium text-ink-black">{{ $journalEntry->entry_number }}</h1>
                <p class="text-body-sm text-slate-gray">{{ $journalEntry->entry_date->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <x-badge :status="$journalEntry->status === 'posted' ? 'success' : 'danger'">
                    {{ ucfirst($journalEntry->status) }}
                </x-badge>
                @can('finance.journal.void')
                    @if ($journalEntry->status === 'posted')
                        <x-button variant="danger" onclick="openVoidModal()">
                            Void Entry
                        </x-button>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 rounded-input border-l-4 border-success bg-success-bg text-success px-4 py-3 text-body-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-input border-l-4 border-danger bg-danger-bg text-danger px-4 py-3 text-body-sm">{{ session('error') }}</div>
    @endif

    @if ($journalEntry->status === 'void')
        <div class="mb-4 rounded-input border-l-4 border-danger bg-danger-bg text-danger px-4 py-3 text-body-sm">
            <strong>Void reason:</strong> {{ $journalEntry->void_reason }}
        </div>
    @endif

    @if ($journalEntry->description)
        <p class="mb-4 text-body text-slate-gray">{{ $journalEntry->description }}</p>
    @endif

    <div class="bg-paper-white border border-border-gray rounded-card shadow-subtle overflow-hidden">
        <table class="w-full text-body">
            <thead class="bg-fog-white text-label text-slate-gray">
                <tr>
                    <th class="text-left px-4 py-3">Akun</th>
                    <th class="text-left px-4 py-3">Deskripsi</th>
                    <th class="text-right px-4 py-3">Debit</th>
                    <th class="text-right px-4 py-3">Credit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($journalEntry->lines as $line)
                    <tr class="border-b border-border-gray">
                        <td class="px-4 py-3">{{ $line->account->code }} - {{ $line->account->name }}</td>
                        <td class="px-4 py-3 text-body-sm text-slate-gray">{{ $line->description ?? '-' }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-medium bg-fog-white">
                    <td colspan="2" class="px-4 py-3 text-right text-label text-slate-gray">Total</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($journalEntry->lines->sum('debit'), 2) }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($journalEntry->lines->sum('credit'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div id="void-modal" class="hidden fixed inset-0 z-50 items-center justify-center" style="background: rgba(23,25,28,0.4)">
        <div class="bg-paper-white rounded-card shadow-elevated max-w-md w-full p-6">
            <h2 class="text-heading-sm font-medium text-ink-black mb-4">Void Journal Entry</h2>
            <form method="POST" action="{{ route('finance.journal-entries.void', $journalEntry) }}">
                @csrf
                <label class="block text-label text-slate-gray mb-1">Alasan void</label>
                <textarea name="void_reason" rows="3" required minlength="5"
                    class="w-full rounded-input border border-border-gray px-3 py-2 text-body focus:border-ink-black focus:shadow-focus-ring outline-none"></textarea>
                @error('void_reason') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror

                <div class="flex justify-end gap-3 mt-4">
                    <x-button variant="secondary" onclick="closeVoidModal()">
                        Batal
                    </x-button>
                    <x-button variant="danger" type="submit">
                        Ya, Void Entry
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<script>
    function openVoidModal() {
        document.getElementById('void-modal').classList.remove('hidden');
        document.getElementById('void-modal').classList.add('flex');
    }
    function closeVoidModal() {
        document.getElementById('void-modal').classList.add('hidden');
        document.getElementById('void-modal').classList.remove('flex');
    }
</script>
