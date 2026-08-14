<x-app-layout>
    <x-slot name="header">Buat Periode Gaji</x-slot>

    <form method="POST" action="{{ route('hr.payroll-runs.store') }}" class="max-w-md space-y-4">
        @csrf

        <div>
            <label class="text-label text-slate-gray block mb-1">Bulan</label>
            <select name="period_month" class="w-full rounded-input border-border-gray">
                @foreach (range(1, 12) as $month)
                    <option value="{{ $month }}" {{ old('period_month') == $month ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(2000, $month, 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <x-input name="period_year" type="number" label="Tahun" required :value="old('period_year') ?? now()->year" />

        <div class="flex gap-2">
            <x-button type="submit" variant="primary">Buat</x-button>
            <x-button variant="danger" href="{{ route('hr.payroll-runs.index') }}">Batal</x-button>
        </div>
    </form>
</x-app-layout>
