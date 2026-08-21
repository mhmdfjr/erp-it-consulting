<x-app-layout>
    <x-slot name="header">
        Komponen Gaji: {{ $employee->full_name }}
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

    <div class="max-w-4xl space-y-6">
        {{-- Employee Header Summary Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-card bg-primary-tint text-primary font-bold flex items-center justify-center text-body shrink-0 shadow-subtle">
                    {{ strtoupper(substr($employee->full_name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="font-mono text-caption font-bold text-primary bg-primary-tint/60 px-2 py-0.5 rounded-input border border-primary/20">
                            {{ $employee->employee_code }}
                        </span>
                        <h2 class="text-heading-sm font-bold text-ink-black tracking-tight">{{ $employee->full_name }}</h2>
                    </div>
                    <p class="text-caption text-slate-gray mt-1 flex items-center gap-2 font-medium">
                        <span>Jabatan: <strong class="text-ink-black">{{ $employee->position?->title ?? '-' }}</strong></span>
                        <span>•</span>
                        <span>Gaji Pokok: <strong class="text-primary tabular-nums">Rp {{ number_format($employee->base_salary, 2, ',', '.') }}</strong></span>
                    </p>
                </div>
            </div>

            <x-button variant="secondary" size="sm" href="{{ route('hr.employees.index') }}" class="gap-1.5 self-start md:self-auto">
                <x-dynamic-component component="lucide-arrow-left" class="w-4 h-4" />
                <span>Kembali ke Daftar</span>
            </x-button>
        </div>

        {{-- Form Tambah Komponen Gaji --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
            <div class="border-b border-border-gray/60 pb-3 mb-5">
                <h3 class="text-heading-sm font-semibold text-ink-black tracking-tight">Tambah Komponen Tunjangan / Potongan</h3>
                <p class="text-caption text-slate-gray mt-0.5">Atur tunjangan tetap (misal: transport, makan) atau potongan berkala.</p>
            </div>

            <form method="POST" action="{{ route('hr.employees.payroll-components.store', $employee) }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Nama Komponen --}}
                    <div>
                        <label for="name" class="block text-label font-medium text-slate-gray mb-1.5">Nama Komponen <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            required
                            placeholder="Contoh: Tunjangan Makan"
                            value="{{ old('name') }}"
                            class="w-full rounded-input border bg-paper-white px-3.5 py-2 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('name') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                        />
                        @error('name')
                            <p class="mt-1 text-caption font-medium text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tipe Komponen --}}
                    <div>
                        <label for="type" class="block text-label font-medium text-slate-gray mb-1.5">Tipe <span class="text-danger">*</span></label>
                        <select
                            name="type"
                            id="type"
                            class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2 text-body-sm text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                        >
                            <option value="allowance">Tunjangan (+)</option>
                            <option value="deduction">Potongan (-)</option>
                        </select>
                    </div>

                    {{-- Nominal Amount --}}
                    <div>
                        <label for="amount" class="block text-label font-medium text-slate-gray mb-1.5">Nominal (Rp) <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="amount"
                            id="amount"
                            required
                            placeholder="0.00"
                            value="{{ old('amount') }}"
                            class="w-full rounded-input border bg-paper-white px-3.5 py-2 text-body-sm font-bold text-ink-black tabular-nums transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('amount') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                        />
                        @error('amount')
                            <p class="mt-1 text-caption font-medium text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end pt-3">
                    <x-button type="submit" variant="primary" size="md" class="gap-1.5">
                        <x-dynamic-component component="lucide-plus" class="w-4 h-4" />
                        <span>Tambah Komponen</span>
                    </x-button>
                </div>
            </form>
        </div>

        {{-- Active Payroll Components Table Card --}}
        <x-data-table
            title="Daftar Komponen Gaji Terdaftar"
            subtitle="Komponen berikut akan otomatis diperhitungkan dalam kalkulasi payroll bulanan"
            :headers="['Nama Komponen', 'Tipe', 'Nominal', 'Aksi']"
            :empty="$components->isEmpty()"
        >
            <x-slot name="emptyState">
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <x-dynamic-component component="lucide-banknote" class="w-8 h-8 text-ash-gray mb-2" />
                    <p class="text-body font-medium text-ink-black">Belum Ada Komponen Tambahan</p>
                    <p class="text-caption text-slate-gray mt-0.5">Karyawan ini hanya menerima Gaji Pokok standar.</p>
                </div>
            </x-slot>

            @foreach ($components as $component)
                @php
                    $isAllowance = $component->type === 'allowance';
                @endphp
                <tr class="hover:bg-mist-gray/40 transition-colors">
                    {{-- Nama Komponen --}}
                    <td class="px-6 py-4 text-body-sm font-medium text-ink-black">
                        {{ $component->name }}
                    </td>

                    {{-- Tipe --}}
                    <td class="px-6 py-4">
                        <x-badge
                            :status="$isAllowance ? 'success' : 'danger'"
                            variant="subtle"
                        >
                            {{ $isAllowance ? 'Tunjangan (+)' : 'Potongan (-)' }}
                        </x-badge>
                    </td>

                    {{-- Nominal --}}
                    <td class="px-6 py-4 text-body-sm font-bold tabular-nums {{ $isAllowance ? 'text-success' : 'text-danger' }}">
                        {{ $isAllowance ? '+' : '-' }} Rp {{ number_format($component->amount, 2, ',', '.') }}
                    </td>

                    {{-- Aksi --}}
                    <td class="px-6 py-4 text-right">
                        <form method="POST" action="{{ route('hr.employees.payroll-components.destroy', [$employee, $component]) }}" class="inline" onsubmit="return confirm('Hapus komponen gaji ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-input text-danger hover:bg-danger-bg transition" title="Hapus Komponen">
                                <x-dynamic-component component="lucide-trash-2" class="w-4 h-4" />
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
</x-app-layout>
