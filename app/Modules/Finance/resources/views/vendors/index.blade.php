<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Vendors</h1>

            @can('finance.vendor.manage')
                <x-button href="{{ route('finance.vendors.create') }}" variant="primary">
                    + Tambah Vendor
                </x-button>
            @endcan
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 rounded-input border-l-4 border-success bg-success-bg text-success px-4 py-3 text-body-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-input border-l-4 border-danger bg-danger-bg text-danger px-4 py-3 text-body-sm">
            {{ session('error') }}
        </div>
    @endif

    <x-data-table
        :headers="['Nama', 'NPWP', 'Telepon', 'Email', 'Aksi']"
        :empty="$vendors->isEmpty()"
    >
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada vendor</p>
                <p class="text-body-sm text-slate-gray">
                    Tambahkan vendor pertama untuk mulai mencatat transaksi pembelian.
                </p>

                @can('finance.vendor.manage')
                    <x-button href="{{ route('finance.vendors.create') }}" variant="primary">
                        + Tambah Vendor
                    </x-button>
                @endcan
            </div>
        </x-slot>

        @foreach ($vendors as $vendor)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3 font-medium">
                    {{ $vendor->name }}
                </td>

                <td class="px-4 py-3">
                    {{ $vendor->npwp ?: '-' }}
                </td>

                <td class="px-4 py-3">
                    {{ $vendor->phone ?: '-' }}
                </td>

                <td class="px-4 py-3">
                    {{ $vendor->email ?: '-' }}
                </td>

                <td class="px-4 py-3">
                    <div class="flex items-center justify-start gap-3">
                        @can('finance.vendor.manage')
                            <a
                                href="{{ route('finance.vendors.edit', $vendor) }}"
                                class="text-info hover:opacity-70"
                                title="Edit"
                            >
                                <x-lucide-pencil class="w-4 h-4" />
                            </a>

                            <form
                                method="POST"
                                action="{{ route('finance.vendors.destroy', $vendor) }}"
                                onsubmit="return confirm('Hapus vendor ini?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="text-danger hover:opacity-70"
                                    title="Hapus"
                                >
                                    <x-lucide-trash-2 class="w-4 h-4" />
                                </button>
                            </form>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $vendors->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
