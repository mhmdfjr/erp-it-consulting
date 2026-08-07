<x-app-layout>
    <x-slot name="header">Pengaturan Sistem</x-slot>

    @if (session('success'))
        <div class="mb-4 rounded-input bg-success-bg text-success px-4 py-2 text-body-sm">{{ session('success') }}</div>
    @endif

    <x-data-table :headers="['Key', 'Deskripsi', 'Aksi']" :empty="$settings->isEmpty()">
        <x-slot name="emptyState">Belum ada pengaturan.</x-slot>

        @foreach ($settings as $setting)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3 text-body font-mono">{{ $setting->key }}</td>
                <td class="px-4 py-3 text-body-sm text-slate-gray">{{ $setting->description }}</td>
                <td class="px-4 py-3">
                    <a href="{{ route('identity.settings.edit', $setting) }}" class="text-info hover:opacity-70">
                        <x-lucide-pencil class="w-4 h-4" />
                    </a>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $settings->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
