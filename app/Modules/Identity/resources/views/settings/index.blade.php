<x-app-layout>
    <x-slot name="header">Pengaturan Sistem</x-slot>

    @if (session('success'))
        <div class="mb-4 rounded-input bg-success-bg text-success px-4 py-2 text-body-sm">{{ session('success') }}</div>
    @endif

    <x-data-table :headers="['Key', 'Deskripsi', '']" :empty="$settings->isEmpty()">
        <x-slot name="emptyState">Belum ada pengaturan.</x-slot>

        @foreach ($settings as $setting)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3 text-body font-mono">{{ $setting->key }}</td>
                <td class="px-4 py-3 text-body-sm text-slate-gray">{{ $setting->description }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('identity.settings.edit', $setting) }}" class="text-info text-body-sm">Edit</a>
                </td>
            </tr>
        @endforeach
    </x-data-table>

    <div class="mt-4">{{ $settings->links() }}</div>
</x-app-layout>
