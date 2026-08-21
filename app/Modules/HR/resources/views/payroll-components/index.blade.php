<x-app-layout>
    <x-slot name="header">
        Kelola Komponen Gaji (Master Payroll)
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

    {{-- Payroll Components Table Card --}}
    <x-data-table
        title="Daftar Master Komponen Gaji"
        subtitle="Atur formula tunjangan penambah dan potongan pengurangan untuk perhitungan slip gaji"
        :headers="['Nama Komponen', 'Tipe Komponen', 'Metode Perhitungan', 'Status', 'Aksi']"
        :empty="$components->isEmpty()"
    >
        <x-slot name="action">
            @can('hr.payrollcomponent.manage')
                <x-button variant="primary" size="sm" href="{{ route('hr.payroll-components.create') }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-plus-circle" class="w-4 h-4" />
                    <span>Tambah Komponen</span>
                </x-button>
            @endcan
        </x-slot>

        <x-slot name="emptyState">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                    <x-dynamic-component component="lucide-wallet" class="w-6 h-6" />
                </div>
                <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Komponen Gaji</h4>
                <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Tambahkan master tunjangan operasional atau potongan BPJS pertama.</p>
                @can('hr.payrollcomponent.manage')
                    <x-button variant="primary" href="{{ route('hr.payroll-components.create') }}" size="sm">
                        + Tambah Komponen Gaji
                    </x-button>
                @endcan
            </div>
        </x-slot>

        @foreach ($components as $payrollComponent)
            @php
                $isEarning = $payrollComponent->type === 'earning';
            @endphp
            <tr class="hover:bg-mist-gray/40 transition-colors">
                {{-- Nama Komponen & Icon --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-card {{ $isEarning ? 'bg-success-bg text-success' : 'bg-danger-bg text-danger' }} font-bold flex items-center justify-center shrink-0 shadow-subtle">
                            <x-dynamic-component :component="$isEarning ? 'lucide-arrow-up-right' : 'lucide-arrow-down-left'" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-body-sm font-semibold text-ink-black leading-tight">{{ $payrollComponent->name }}</p>
                            <span class="text-[11px] text-slate-gray">Komponen Master Payroll</span>
                        </div>
                    </div>
                </td>

                {{-- Tipe Komponen --}}
                <td class="px-6 py-4">
                    <x-badge
                        :status="$isEarning ? 'success' : 'danger'"
                        variant="subtle"
                    >
                        {{ $isEarning ? 'Earning (Penambah)' : 'Deduction (Pengurang)' }}
                    </x-badge>
                </td>

                {{-- Metode Perhitungan --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-1.5">
                        <span class="inline-flex items-center gap-1.5 text-caption font-semibold text-slate-gray bg-fog-white border border-border-gray/80 px-2.5 py-1 rounded-input">
                            <x-dynamic-component :component="$payrollComponent->calculation_type === 'fixed_amount' ? 'lucide-hash' : 'lucide-percent'" class="w-3.5 h-3.5 text-ash-gray" />
                            {{ $payrollComponent->calculation_type === 'fixed_amount' ? 'Nominal Tetap (Rp)' : 'Persentase Base Salary' }}
                        </span>
                    </div>
                </td>

                {{-- Status Aktif --}}
                <td class="px-6 py-4">
                    <x-badge
                        :status="$payrollComponent->is_active ? 'success' : 'neutral'"
                        variant="solid"
                    >
                        {{ $payrollComponent->is_active ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </td>

                {{-- Aksi --}}
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end">
                        @can('hr.payrollcomponent.manage')
                            <a href="{{ route('hr.payroll-components.edit', $payrollComponent) }}"
                               class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                               title="Edit Komponen">
                                <x-dynamic-component component="lucide-pencil" class="w-4 h-4" />
                            </a>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $components->links() }}
        </x-slot>
    </x-data-table>
</x-app-layout>
