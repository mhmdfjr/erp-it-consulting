<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-heading font-medium text-ink-black">Kelola Komponen Gaji</h1>
            @can('hr.payrollcomponent.manage')
                <x-button variant="primary" href="{{ route('hr.payroll-components.create') }}">
                    + Tambah Komponen Gaji
                </x-button>
            @endcan
        </div>
    </x-slot>

    <x-data-table :headers="['Nama', 'Tipe', 'Metode Hitung', 'Status', 'Aksi']" :empty="$components->isEmpty()">
        <x-slot name="emptyState">
            <div class="flex flex-col items-center gap-2">
                <p class="text-heading-sm">Belum ada komponen gaji</p>
                <p class="text-body-sm text-slate-gray">Tambahkan tunjangan atau potongan tetap pertama.</p>
                <x-button variant="primary" href="{{ route('hr.payroll-components.create') }}">+ Tambah Komponen Gaji</x-button>
            </div>
        </x-slot>

        @foreach ($components as $payrollComponent)
            <tr class="hover:bg-mist-gray">
                <td class="px-4 py-3">{{ $payrollComponent->name }}</td>
                <td class="px-4 py-3">
                    <x-badge status="{{ $payrollComponent->type === 'earning' ? 'success' : 'danger' }}">
                        {{ ucfirst($payrollComponent->type) }}
                    </x-badge>
                </td>
                <td class="px-4 py-3">
                    {{ $payrollComponent->calculation_type === 'fixed_amount' ? 'Nominal Tetap' : 'Persentase Base Salary' }}
                </td>
                <td class="px-4 py-3">
                    <x-badge status="{{ $payrollComponent->is_active ? 'success' : 'info' }}">
                        {{ $payrollComponent->is_active ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </td>
                <td class="px-4 py-3">
                    @can('hr.payrollcomponent.manage')
                        <a href="{{ route('hr.payroll-components.edit', $payrollComponent) }}" class="text-info hover:opacity-70">
                            <x-lucide-pencil class="w-4 h-4" />
                        </a>
                    @endcan
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $components->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
