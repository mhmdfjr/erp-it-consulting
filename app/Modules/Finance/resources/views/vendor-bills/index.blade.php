<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Daftar Tagihan Vendor</h1>

            @can('finance.vendorbill.create')
                <x-button href="{{ route('finance.vendor-bills.create') }}" variant="primary">
                    + Buat Tagihan Vendor
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
        :headers="['No. Bill', 'Vendor', 'Tanggal', 'Jatuh Tempo', 'Jumlah', 'Status', 'Aksi']"
        :empty="$bills->isEmpty()"
    >
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada Vendor Bill</p>
                <p class="text-body-sm text-slate-gray">
                    Buat vendor bill pertama untuk mulai mencatat tagihan dari vendor.
                </p>

                @can('finance.vendorbill.create')
                    <x-button href="{{ route('finance.vendor-bills.create') }}" variant="primary">
                        + Buat Tagihan Vendor
                    </x-button>
                @endcan
            </div>
        </x-slot>

        @foreach ($bills as $bill)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3 font-medium tabular-nums">
                    {{ $bill->bill_number }}
                </td>

                <td class="px-4 py-3">
                    {{ $bill->vendor->name }}
                </td>

                <td class="px-4 py-3">
                    {{ $bill->bill_date->format('d M Y') }}
                </td>

                <td class="px-4 py-3">
                    {{ $bill->due_date->format('d M Y') }}
                </td>

                <td class="px-4 py-3 tabular-nums">
                    Rp {{ number_format($bill->amount, 0, ',', '.') }}
                </td>

                <td class="px-4 py-3">
                    <x-badge :status="match ($bill->status) {
                        'paid' => 'success',
                        'draft' => 'info',
                        'approved' => 'warning',
                        'void' => 'danger',
                        default => 'secondary',
                    }">
                        {{ ucfirst($bill->status) }}
                    </x-badge>
                </td>

                <td class="px-4 py-3">
                    <div class="flex items-center justify-start gap-3">
                        <a
                            href="{{ route('finance.vendor-bills.show', $bill) }}"
                            class="text-info hover:opacity-70"
                            title="Lihat Detail"
                        >
                            <x-lucide-eye class="w-4 h-4" />
                        </a>

                        @can('finance.vendorbill.edit')
                            <a
                                href="{{ route('finance.vendor-bills.edit', $bill) }}"
                                class="text-info hover:opacity-70"
                                title="Edit"
                            >
                                <x-lucide-pencil class="w-4 h-4" />
                            </a>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $bills->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
