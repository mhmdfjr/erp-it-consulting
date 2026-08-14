<x-app-layout>
    <x-slot name="header">Edit Jabatan</x-slot>

    <form method="POST" action="{{ route('hr.positions.update', $position) }}" class="max-w-xl space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="text-label text-slate-gray block mb-1">Department</label>
            <select name="department_id" class="w-full rounded-input border-border-gray">
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" {{ (old('department_id') ?? $position->department_id) == $department->id ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <x-input name="title" label="Title" required :value="old('title') ?? $position->title" />

        <div class="flex gap-2">
            <x-button type="submit" variant="primary">Simpan</x-button>
            <x-button variant="danger" href="{{ route('hr.positions.index') }}">Batal</x-button>
        </div>
    </form>
</x-app-layout>
