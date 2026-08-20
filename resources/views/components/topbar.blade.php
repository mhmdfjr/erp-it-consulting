<header class="h-topbar bg-paper-white/80 backdrop-blur-md border-b border-border-gray flex items-center justify-between px-6 shrink-0 sticky top-0 z-20">
    <!-- Breadcrumb -->
    <div class="flex flex-col justify-center">
        <nav class="text-caption text-slate-gray flex items-center gap-1.5 font-normal">
            <span class="text-ash-gray">/</span>
            <span class="text-ink-black font-medium capitalize">{{ request()->route()?->getName() ? str_replace(['.', '-', 'index'], [' / ', ' ', ''], request()->route()->getName()) : 'Dashboard' }}</span>
        </nav>
    </div>

    <!-- Right Controls: Search, Quick Actions, Profile -->
    <div class="flex items-center gap-4">
        <!-- Search Input -->
        <div class="relative hidden sm:block w-48 lg:w-60">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-ash-gray">
                <x-dynamic-component component="lucide-search" class="w-4 h-4" />
            </span>
            <input
                type="text"
                placeholder="Type here..."
                class="w-full rounded-input border border-border-gray bg-fog-white pl-9 pr-3.5 py-1.5 text-body-sm text-ink-black placeholder-ash-gray transition focus:border-primary focus:bg-paper-white focus:outline-none focus:shadow-focus-ring"
            />
        </div>

        <!-- Quick Action Icons -->
        <div class="flex items-center gap-2 text-slate-gray">
            <button type="button" class="p-2 rounded-input hover:bg-mist-gray hover:text-primary transition" title="Settings">
                <x-dynamic-component component="lucide-settings" class="w-4 h-4" />
            </button>
            <button type="button" class="p-2 rounded-input hover:bg-mist-gray hover:text-primary transition relative" title="Notifications">
                <x-dynamic-component component="lucide-bell" class="w-4 h-4" />
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-primary rounded-full ring-2 ring-paper-white"></span>
            </button>
        </div>

        <!-- User Profile Dropdown -->
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="flex items-center gap-2.5 pl-2 py-1 rounded-input hover:bg-mist-gray transition group">
                    <span class="h-8 w-8 rounded-full bg-primary text-paper-white flex items-center justify-center text-caption font-semibold shadow-subtle group-hover:bg-primary-hover">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </span>
                    <span class="hidden md:inline-block text-body-sm font-medium text-ink-black group-hover:text-primary transition">
                        {{ auth()->user()->name ?? 'User' }}
                    </span>
                    <x-dynamic-component component="lucide-chevron-down" class="w-3.5 h-3.5 text-slate-gray" />
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="px-4 py-2 border-b border-border-gray">
                    <p class="text-caption font-semibold text-ink-black truncate">{{ auth()->user()->name ?? 'User' }}</p>
                    <p class="text-caption text-slate-gray truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>

                <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2">
                    <x-dynamic-component component="lucide-user" class="w-4 h-4" />
                    {{ __('Profil') }}
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')"
                        class="flex items-center gap-2 text-danger hover:bg-danger-bg"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        <x-dynamic-component component="lucide-log-out" class="w-4 h-4" />
                        {{ __('Keluar') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
