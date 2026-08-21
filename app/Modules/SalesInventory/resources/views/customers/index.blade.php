<x-app-layout>
    <x-slot name="header">
        Daftar Pelanggan (Customers)
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

    {{-- Customers Table Card --}}
    <x-data-table
        title="Daftar Pelanggan & Klien"
        subtitle="Kelola basis data pelanggan individu dan entitas korporat untuk transaksi penjualan"
        :headers="['Pelanggan & Email', 'Tipe Klien', 'No. Telepon', 'Alamat', 'Aksi']"
        :empty="$customers->isEmpty()"
    >
        <x-slot name="action">
            @can('sales.customer.create')
                <x-button variant="primary" size="sm" href="{{ route('sales.customers.create') }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-user-plus" class="w-4 h-4" />
                    <span>Tambah Pelanggan</span>
                </x-button>
            @endcan
        </x-slot>

        <x-slot name="emptyState">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                    <x-dynamic-component component="lucide-users" class="w-6 h-6" />
                </div>
                <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Pelanggan</h4>
                <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Tambahkan data pelanggan pertama untuk mulai membuat pesanan dan penawaran penjualan.</p>
                @can('sales.customer.create')
                    <x-button variant="primary" href="{{ route('sales.customers.create') }}" size="sm">
                        + Tambah Pelanggan
                    </x-button>
                @endcan
            </div>
        </x-slot>

        @foreach ($customers as $customer)
            <tr class="hover:bg-mist-gray/40 transition-colors">
                {{-- Avatar & Nama Customer --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-card {{ $customer->customer_type === 'corporate' ? 'bg-primary-tint text-primary' : 'bg-accent-peach-tint text-[#c2410c]' }} font-bold flex items-center justify-center text-caption shrink-0 shadow-subtle">
                            {{ strtoupper(substr($customer->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-body-sm font-semibold text-ink-black leading-tight">{{ $customer->name }}</p>
                            <p class="text-caption text-slate-gray font-medium">{{ $customer->email ?: 'Tanpa email' }}</p>
                        </div>
                    </div>
                </td>

                {{-- Tipe Customer --}}
                <td class="px-6 py-4">
                    <x-badge
                        :status="$customer->customer_type === 'corporate' ? 'primary' : 'peach'"
                        variant="subtle"
                    >
                        {{ $customer->customer_type === 'corporate' ? 'Perusahaan' : 'Individu' }}
                    </x-badge>
                </td>

                {{-- Telepon --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-1.5 text-body-sm text-ink-black font-medium tabular-nums">
                        @if ($customer->phone)
                            <x-dynamic-component component="lucide-phone" class="w-3.5 h-3.5 text-ash-gray" />
                            <span>{{ $customer->phone }}</span>
                        @else
                            <span class="text-slate-gray">-</span>
                        @endif
                    </div>
                </td>

                {{-- Alamat --}}
                <td class="px-6 py-4">
                    <p class="text-body-sm text-slate-gray leading-snug line-clamp-1 max-w-xs">
                        {{ $customer->address ?: '-' }}
                    </p>
                </td>

                {{-- Aksi --}}
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="{{ route('sales.customers.edit', $customer) }}"
                           class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                           title="Edit Pelanggan">
                            <x-dynamic-component component="lucide-pencil" class="w-4 h-4" />
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $customers->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
