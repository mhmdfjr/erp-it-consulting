<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Vendor Bills</h1>
            @can('finance.vendorbill.create')
                <x-button href="{{ route('finance.vendor-bills.create') }}" variant="primary">+ Buat Vendor Bill</x-button>
            @endcan
        </div>
    </x-slot>

    @if (session('success')) <div class="mb-4 rounded-input border-l-4 border-success bg-success-bg text-success px-4 py-3 text-body-sm">{{ session('success') }}</div> @endif

    <div class="bg-paper-white border border-border-gray rounded-card shadow-subtle overflow-hidden">
        <table class="w-full text-body">
            <thead class="bg-fog-white text-label text-slate-gray">
                <tr>
                    <th class="text-left px-4 py-3">No. Bill</th>
                    <th class="text-left px-4 py-3">Vendor</th>
                    <th class="text-left px-4 py-3">Tanggal</th>
                    <th class="text-left px-4 py-3">Jatuh Tempo</th>
                    <th class="text-right px-4 py-3">Jumlah</th>
                    <th class="text-left px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bills as $bill)
                    <tr class="border-b border-border-gray hover:bg-mist-gray cursor-pointer" onclick="window.location='{{ route('finance.vendor-bills.show', $bill) }}'">
                        <td class="px-4 py-3">{{ $bill->bill_number }}</td>
                        <td class="px-4 py-3">{{ $bill->vendor->name }}</td>
                        <td class="px-4 py-3">{{ $bill->bill_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $bill->due_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ number_format($bill->amount, 2) }}</td>
                        <td class="px-4 py-3">
                            <x-badge :status="$bill->status === 'paid' ? 'success' : ($bill->status === 'void' ? 'danger' : 'warning')">
                                {{ ucfirst($bill->status) }}
                            </x-badge>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-16 text-center text-body-sm text-slate-gray">Belum ada vendor bill.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $bills->links() }}</div>
</x-app-layout>
