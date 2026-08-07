<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Journal Entries</h1>

            @can('finance.journal.create')
                <x-button href="{{ route('finance.journal-entries.create') }}" variant="primary">
                    + Buat Journal Entry
                </x-button>
            @endcan
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 rounded-input border-l-4 border-success bg-success-bg text-success px-4 py-3 text-body-sm">
            {{ session('success') }}
        </div>
    @endif

    <x-data-table
        :headers="['No. Entry', 'Tanggal', 'Deskripsi', 'Referensi', 'Status', 'Aksi']"
        :empty="$entries->isEmpty()"
    >
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada Journal Entry</p>
                <p class="text-body-sm text-slate-gray">
                    Buat journal entry pertama untuk mulai mencatat transaksi.
                </p>

                @can('finance.journal.create')
                    <x-button href="{{ route('finance.journal-entries.create') }}" variant="primary">
                        + Buat Journal Entry
                    </x-button>
                @endcan
            </div>
        </x-slot>

        @foreach ($entries as $entry)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3 tabular-nums font-medium">
                    {{ $entry->entry_number }}
                </td>

                <td class="px-4 py-3">
                    {{ $entry->entry_date->format('d M Y') }}
                </td>

                <td class="px-4 py-3">
                    {{ $entry->description ?: '-' }}
                </td>

                <td class="px-4 py-3 text-body-sm text-slate-gray">
                    {{ $entry->reference_type
                        ? class_basename($entry->reference_type) . ' #' . $entry->reference_id
                        : 'Manual' }}
                </td>

                <td class="px-4 py-3">
                    <x-badge :status="$entry->status === 'posted' ? 'success' : 'warning'">
                        {{ ucfirst($entry->status) }}
                    </x-badge>
                </td>

                <td class="px-4 py-3 text-right">
                    <a
                        href="{{ route('finance.journal-entries.show', $entry) }}"
                        class="text-info hover:opacity-70"
                        title="Lihat Detail"
                    >
                        <x-lucide-eye class="w-4 h-4" />
                    </a>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $entries->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
