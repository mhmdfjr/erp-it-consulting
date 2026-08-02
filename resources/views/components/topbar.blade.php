<header class="h-topbar bg-paper-white border-b border-border-gray flex items-center justify-between px-6 shrink-0">
    <nav class="text-body-sm text-slate-gray">
        {{ $header ?? '' }}
    </nav>

    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button class="flex items-center gap-2 text-body-sm text-slate-gray hover:text-ink-black">
                <span class="h-8 w-8 rounded-full bg-ink-black text-paper-white flex items-center justify-center text-caption font-medium">
                    {{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}
                </span>
                {{ auth()->user()->name ?? '' }}
            </button>
        </x-slot>

        <x-slot name="content">
            <x-dropdown-link :href="route('profile.edit')">{{ __('Profil') }}</x-dropdown-link>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dropdown-link :href="route('logout')"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                    {{ __('Keluar') }}
                </x-dropdown-link>
            </form>
        </x-slot>
    </x-dropdown>
</header>
