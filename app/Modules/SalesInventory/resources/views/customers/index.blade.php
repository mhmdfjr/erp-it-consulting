<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Daftar Pelanggan</h1>
            @can('sales.customer.create')
                <x-button variant="primary" href="{{ route('sales.customers.create') }}">
                    + Tambah Pelanggan
                </x-button>
            @endcan
        </div>
    </x-slot>

    <x-data-table :headers="['Nama', 'Tipe', 'Telepon', 'Email', 'Aksi']" :empty="$customers->isEmpty()">
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada customer</p>
                <p class="text-body-sm text-slate-gray">Tambahkan customer pertama.</p>
                <x-button variant="primary" href="{{ route('sales.customers.create') }}">+ Tambah Customer</x-button>
            </div>
        </x-slot>

        @foreach ($customers as $customer)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3">{{ $customer->name }}</td>
                <td class="px-4 py-3">
                    <x-badge status="{{ $customer->customer_type === 'individual' ? 'info' : 'success' }}">
                        {{ $customer->customer_type === 'individual' ? 'Individu' : 'Perusahaan' }}
                    </x-badge>
                </td>
                <td class="px-4 py-3">{{ $customer->phone ?? '-' }}</td>
                <td class="px-4 py-3">{{ $customer->email ?? '-' }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('sales.customers.edit', $customer) }}" class="text-info hover:opacity-70">
                        <x-lucide-pencil class="w-4 h-4" />
                    </a>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $customers->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
