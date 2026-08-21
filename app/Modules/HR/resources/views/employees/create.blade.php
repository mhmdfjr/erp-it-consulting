<x-app-layout>
    <x-slot name="header">
        Tambah Karyawan Baru
    </x-slot>

    <div class="max-w-4xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Formulir Pendaftaran Karyawan</h3>
            <p class="text-caption text-slate-gray mt-0.5">Lengkapi identitas pribadi, penempatan posisi jabatan, dan informasi rekening penggajian.</p>
        </div>

        <form method="POST" action="{{ route('hr.employees.store') }}" class="space-y-6">
            @csrf

            {{-- Bagian 1: Identitas Pokok & Kontak --}}
            <div>
                <span class="text-[11px] font-bold text-primary uppercase tracking-wider block mb-3">1. Identitas Pokok & Pribadi</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input
                        name="employee_code"
                        label="Nomor Induk Karyawan (NIK / Code)"
                        placeholder="Contoh: EMP-2026-001"
                        required
                        :value="old('employee_code') ?? ''"
                    />

                    <x-input
                        name="full_name"
                        label="Nama Lengkap Karyawan"
                        placeholder="Nama lengkap beserta gelar (jika ada)"
                        required
                        :value="old('full_name') ?? ''"
                    />

                    <x-input
                        name="nik"
                        label="Nomor KTP (NIK Kependudukan)"
                        placeholder="16 digit nomor KTP"
                        :value="old('nik') ?? ''"
                    />

                    <x-input
                        name="npwp"
                        label="NPWP Pribadi"
                        placeholder="Contoh: 01.234.567.8-901.000"
                        :value="old('npwp') ?? ''"
                    />

                    <div>
                        <label for="gender" class="block text-label font-medium text-slate-gray mb-1.5">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select
                            name="gender"
                            id="gender"
                            class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                        >
                            <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                        </select>
                    </div>

                    <x-input
                        name="birth_date"
                        type="date"
                        label="Tanggal Lahir"
                        required
                        :value="old('birth_date') ?? ''"
                    />

                    <x-input
                        name="phone"
                        label="No. Telepon / WhatsApp"
                        placeholder="08123456789"
                        :value="old('phone') ?? ''"
                    />

                    <x-input
                        name="email"
                        type="email"
                        label="Alamat Email"
                        placeholder="karyawan@perusahaan.com"
                        :value="old('email') ?? ''"
                    />
                </div>

                <div class="mt-4">
                    <x-input
                        name="address"
                        label="Alamat Tempat Tinggal Lengkap"
                        placeholder="Tuliskan alamat domisili lengkap"
                        :value="old('address') ?? ''"
                    />
                </div>
            </div>

            <div class="h-px bg-border-gray/60"></div>

            {{-- Bagian 2: Penempatan Kerja & Status Pajak --}}
            <div>
                <span class="text-[11px] font-bold text-primary uppercase tracking-wider block mb-3">2. Posisi Jabatan, Pajak & Gaji Pokok</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="position_id" class="block text-label font-medium text-slate-gray mb-1.5">Posisi Jabatan & Divisi <span class="text-danger">*</span></label>
                        <select
                            name="position_id"
                            id="position_id"
                            required
                            class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('position_id') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                        >
                            <option value="">-- Pilih Posisi Jabatan --</option>
                            @foreach ($departments as $department)
                                <optgroup label="Divisi: {{ $department->name }}">
                                    @foreach ($department->positions as $position)
                                        <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>
                                            {{ $position->title }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('position_id')
                            <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="ptkp_status" class="block text-label font-medium text-slate-gray mb-1.5">Status PTKP Pajak PPh 21 <span class="text-danger">*</span></label>
                        <select
                            name="ptkp_status"
                            id="ptkp_status"
                            class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                        >
                            @foreach (['TK0', 'TK1', 'TK2', 'TK3', 'K0', 'K1', 'K2', 'K3'] as $status)
                                <option value="{{ $status }}" @selected(old('ptkp_status', 'TK0') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="base_salary" class="block text-label font-medium text-slate-gray mb-1.5">Gaji Pokok / Base Salary (Rp) <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="base_salary"
                            id="base_salary"
                            required
                            placeholder="0.00"
                            value="{{ old('base_salary') ?? '' }}"
                            class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body font-bold text-ink-black tabular-nums transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('base_salary') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                        />
                        @error('base_salary')
                            <p class="mt-1.5 text-caption font-medium text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="employment_status" class="block text-label font-medium text-slate-gray mb-1.5">Status Kepegawaian <span class="text-danger">*</span></label>
                        <select
                            name="employment_status"
                            id="employment_status"
                            class="w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2.5 text-body text-ink-black transition focus:ring-0 focus:outline-none focus:border-primary focus:shadow-focus-ring"
                        >
                            <option value="active" @selected(old('employment_status', 'active') === 'active')>Active (Aktif)</option>
                            <option value="resigned" @selected(old('employment_status') === 'resigned')>Resigned (Mengundurkan Diri)</option>
                            <option value="terminated" @selected(old('employment_status') === 'terminated')>Terminated (Berhenti)</option>
                        </select>
                    </div>

                    <x-input
                        name="hire_date"
                        type="date"
                        label="Tanggal Mulai Bekerja (Hire Date)"
                        required
                        :value="old('hire_date', date('Y-m-d'))"
                    />

                    <x-input
                        name="termination_date"
                        type="date"
                        label="Tanggal Berhenti Bekerja (Opsional)"
                        :value="old('termination_date') ?? ''"
                    />
                </div>
            </div>

            <div class="h-px bg-border-gray/60"></div>

            {{-- Bagian 3: Rekening Payroll --}}
            <div>
                <span class="text-[11px] font-bold text-primary uppercase tracking-wider block mb-3">3. Rekening Pembayaran Gaji (Payroll)</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input
                        name="bank_name"
                        label="Nama Bank"
                        placeholder="Contoh: BCA, Mandiri, BRI, BNI"
                        :value="old('bank_name') ?? ''"
                    />

                    <x-input
                        name="bank_account_number"
                        label="Nomor Rekening Bank"
                        placeholder="Contoh: 1234567890"
                        :value="old('bank_account_number') ?? ''"
                    />
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('hr.employees.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Simpan Karyawan
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
