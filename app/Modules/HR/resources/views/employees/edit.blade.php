<x-app-layout>
    <x-slot name="header">Edit Employee</x-slot>

    <form method="POST" action="{{ route('hr.employees.update', $employee) }}" class="max-w-xl space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <x-input name="employee_code" label="Kode Employee" required :value="old('employee_code') ?? $employee->employee_code" />
            <x-input name="full_name" label="Nama Lengkap" required :value="old('full_name') ?? $employee->full_name" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-input name="nik" label="NIK" :value="old('nik') ?? $employee->nik" />
            <x-input name="npwp" label="NPWP" :value="old('npwp') ?? $employee->npwp" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-label text-slate-gray block mb-1">Jenis Kelamin</label>
                <select name="gender" class="w-full rounded-input border-border-gray">
                    <option value="L" {{ (old('gender') ?? $employee->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ (old('gender') ?? $employee->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <x-input name="birth_date" type="date" label="Tanggal Lahir" required :value="old('birth_date') ?? $employee->birth_date->format('Y-m-d')" />
        </div>

        <div>
            <label class="text-label text-slate-gray block mb-1">Position</label>
            <select name="position_id" class="w-full rounded-input border-border-gray">
                <option value="">-- Pilih Position --</option>
                @foreach ($departments as $department)
                    <optgroup label="{{ $department->name }}">
                        @foreach ($department->positions as $position)
                            <option value="{{ $position->id }}" {{ (old('position_id') ?? $employee->position_id) == $position->id ? 'selected' : '' }}>
                                {{ $position->title }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-label text-slate-gray block mb-1">Status PTKP</label>
            <select name="ptkp_status" class="w-full rounded-input border-border-gray">
                @foreach (['TK0', 'TK1', 'TK2', 'TK3', 'K0', 'K1', 'K2', 'K3'] as $status)
                    <option value="{{ $status }}" {{ (old('ptkp_status') ?? $employee->ptkp_status) === $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
        </div>

        <x-input name="base_salary" type="number" step="0.01" label="Base Salary" required :value="old('base_salary') ?? $employee->base_salary" />

        <div class="grid grid-cols-2 gap-4">
            <x-input name="hire_date" type="date" label="Tanggal Bergabung" required :value="old('hire_date') ?? $employee->hire_date->format('Y-m-d')" />
            <x-input name="termination_date" type="date" label="Tanggal Berhenti (opsional)" :value="old('termination_date') ?? $employee->termination_date?->format('Y-m-d')" />
        </div>

        <div>
            <label class="text-label text-slate-gray block mb-1">Status Kepegawaian</label>
            <select name="employment_status" class="w-full rounded-input border-border-gray">
                <option value="active" {{ (old('employment_status') ?? $employee->employment_status) === 'active' ? 'selected' : '' }}>Active</option>
                <option value="resigned" {{ (old('employment_status') ?? $employee->employment_status) === 'resigned' ? 'selected' : '' }}>Resigned</option>
                <option value="terminated" {{ (old('employment_status') ?? $employee->employment_status) === 'terminated' ? 'selected' : '' }}>Terminated</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-input name="bank_name" label="Nama Bank" :value="old('bank_name') ?? $employee->bank_name" />
            <x-input name="bank_account_number" label="Nomor Rekening" :value="old('bank_account_number') ?? $employee->bank_account_number" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-input name="phone" label="Telepon" :value="old('phone') ?? $employee->phone" />
            <x-input name="email" type="email" label="Email" :value="old('email') ?? $employee->email" />
        </div>

        <x-input name="address" label="Alamat" :value="old('address') ?? $employee->address" />

        <div class="flex gap-2">
            <x-button type="submit" variant="primary">Simpan</x-button>
            <x-button variant="danger" href="{{ route('hr.employees.index') }}">Batal</x-button>
            <x-button variant="secondary" href="{{ route('hr.employees.payroll-components.index', $employee) }}">Kelola Payroll Komponen</x-button>
        </div>
    </form>
</x-app-layout>
