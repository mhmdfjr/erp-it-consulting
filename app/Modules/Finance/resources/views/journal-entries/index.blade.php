<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Journal Entries</h1>
            @can('finance.journal.create')
                <x-button href="{{ route('finance.journal-entries.create') }}" variant="primary">+ Buat Journal Entry</x-button>
            @endcan
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 rounded-input border-l-4 border-success bg-success-bg text-success px-4 py-3 text-body-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-paper-white border border-border-gray rounded-card shadow-subtle overflow-hidden">
        <table class="w-full text-body">
            <thead class="bg-fog-white text-label text-slate-gray">
                <tr>
                    <th class="text-left px-4 py-3">No. Entry</th>
                    <th class="text-left px-4 py-3">Tanggal</th>
                    <th class="text-left px-4 py-3">Deskripsi</th>
                    <th class="text-left px-4 py-3">Referensi</th>
                    <th class="text-left px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entries as $entry)
                    <tr class="border-b border-border-gray hover:bg-mist-gray cursor-pointer" onclick="window.location='{{ route('finance.journal-entries.show', $entry) }}'">
                        <td class="px-4 py-3 tabular-nums">{{ $entry->entry_number }}</td>
                        <td class="px-4 py-3">{{ $entry->entry_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $entry->description ?? '-' }}</td>
                        <td class="px-4 py-3 text-body-sm text-slate-gray">
                            {{ $entry->reference_type ? class_basename($entry->reference_type) . ' #' . $entry->reference_id : 'Manual' }}
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :status="$entry->status === 'posted' ? 'success' : 'danger'">
                                {{ ucfirst($entry->status) }}
                            </x-badge>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-16 text-center text-body-sm text-slate-gray">
                            Belum ada journal entry.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $entries->links() }}</div>
</x-app-layout>
