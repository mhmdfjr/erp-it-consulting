<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Vendors</h1>
            @can('finance.vendor.manage')
                <x-button href="{{ route('finance.vendors.create') }}" variant="primary">+ Tambah Vendor</x-button>
            @endcan
        </div>
    </x-slot>

    @if (session('success')) <div class="mb-4 rounded-input border-l-4 border-success bg-success-bg text-success px-4 py-3 text-body-sm">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="mb-4 rounded-input border-l-4 border-danger bg-danger-bg text-danger px-4 py-3 text-body-sm">{{ session('error') }}</div> @endif

    <div class="bg-paper-white border border-border-gray rounded-card shadow-subtle overflow-hidden">
        <table class="w-full text-body">
            <thead class="bg-fog-white text-label text-slate-gray">
                <tr>
                    <th class="text-left px-4 py-3">Nama</th>
                    <th class="text-left px-4 py-3">NPWP</th>
                    <th class="text-left px-4 py-3">Telepon</th>
                    <th class="text-left px-4 py-3">Email</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vendors as $vendor)
                    <tr class="border-b border-border-gray hover:bg-mist-gray">
                        <td class="px-4 py-3">{{ $vendor->name }}</td>
                        <td class="px-4 py-3">{{ $vendor->npwp ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $vendor->phone ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $vendor->email ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('finance.vendor.manage')
                                <a href="{{ route('finance.vendors.edit', $vendor) }}" class="text-info text-body-sm mr-3">Edit</a>
                                <form method="POST" action="{{ route('finance.vendors.destroy', $vendor) }}" class="inline" onsubmit="return confirm('Hapus vendor ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-danger text-body-sm">Hapus</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-16 text-center text-body-sm text-slate-gray">Belum ada vendor.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $vendors->links() }}</div>
</x-app-layout>
