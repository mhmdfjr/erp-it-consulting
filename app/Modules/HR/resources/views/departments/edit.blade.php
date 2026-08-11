<x-app-layout>
    <x-slot name="header">Edit Department</x-slot>

    <form method="POST" action="{{ route('hr.departments.update', $department) }}" class="max-w-xl space-y-4">
        @csrf
        @method('PUT')

        <x-input name="name" label="Nama Department" required :value="old('name') ?? $department->name" />

        <div class="flex gap-2">
            <x-button type="submit" variant="primary">Simpan</x-button>
            <x-button variant="danger" href="{{ route('hr.departments.index') }}">Batal</x-button>
        </div>
    </form>
</x-app-layout>
