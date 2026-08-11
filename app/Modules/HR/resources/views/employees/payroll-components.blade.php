<x-app-layout>
    <x-slot name="header">Payroll Component - {{ $employee->full_name }}</x-slot>

    <div class="mb-6">
        <x-data-table :headers="['Component', 'Nilai', 'Berlaku Sejak', 'Berlaku Sampai', 'Aksi']" :empty="$assignments->isEmpty()">
            <x-slot name="emptyState">
                <p class="text-body-sm text-slate-gray">Belum ada component yang di-assign ke employee ini.</p>
            </x-slot>

            @foreach ($assignments as $assignment)
                <tr class="hover:bg-mist-gray">
                    <td class="px-4 py-3">
                        {{ $assignment->payrollComponent->name }}
                        <x-badge status="{{ $assignment->payrollComponent->type === 'earning' ? 'success' : 'danger' }}">
                            {{ ucfirst($assignment->payrollComponent->type) }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-3 tabular-nums">
                        @if ($assignment->amount !== null)
                            Rp {{ number_format($assignment->amount, 0, ',', '.') }}
                        @else
                            {{ $assignment->percentage }}% dari base salary
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $assignment->effective_date->format('d M Y') }}</td>
                    <td class="px-4 py-3">{{ $assignment->end_date?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('hr.employees.payroll-components.destroy', [$employee, $assignment]) }}"
                              onsubmit="return confirm('Hapus component ini dari employee?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-danger hover:opacity-70">
                                <x-lucide-trash-2 class="w-4 h-4" />
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>

    <form method="POST" action="{{ route('hr.employees.payroll-components.store', $employee) }}"
          x-data="{ selectedType: '' }" class="max-w-xl space-y-4 border-t border-border-gray pt-6">
        @csrf

        <h2 class="text-heading-sm">Tambah Component</h2>

        <div>
            <label class="text-label text-slate-gray block mb-1">Component</label>
            <select name="payroll_component_id" x-model="selectedType" class="w-full rounded-input border-border-gray">
                @foreach ($components as $component)
                    <option value="{{ $component->id }}" data-calc-type="{{ $component->calculation_type }}">
                        {{ $component->name }} ({{ $component->calculation_type === 'fixed_amount' ? 'Nominal' : 'Persentase' }})
                    </option>
                @endforeach
            </select>
        </div>

        <x-input name="amount" type="number" step="0.01" label="Amount (isi kalau component tipe nominal tetap)" :value="old('amount') ?? ''" />
        <x-input name="percentage" type="number" step="0.01" label="Percentage (isi kalau component tipe persentase)" :value="old('percentage') ?? ''" />

        <div class="grid grid-cols-2 gap-4">
            <x-input name="effective_date" type="date" label="Berlaku Sejak" required :value="old('effective_date') ?? now()->toDateString()" />
            <x-input name="end_date" type="date" label="Berlaku Sampai (opsional)" :value="old('end_date') ?? ''" />
        </div>

        <div class="flex gap-2">
            <x-button type="submit" variant="primary">Tambah</x-button>
            <x-button variant="secondary" href="{{ route('hr.employees.edit', $employee) }}">Kembali ke Karyawan</x-button>
        </div>
    </form>
</x-app-layout>
