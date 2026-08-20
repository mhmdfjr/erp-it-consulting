<x-app-layout>
    <x-slot name="header">
        Edit Pengguna: {{ $user->name }}
    </x-slot>

    <div class="max-w-full mx-auto">
        @include('identity::users._form', ['roles' => $roles, 'user' => $user])
    </div>
</x-app-layout>
