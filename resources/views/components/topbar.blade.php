<header class="h-topbar bg-paper-white/80 dark:bg-[#111c44]/80 backdrop-blur-md border-b border-border-gray dark:border-border-gray/10 flex items-center justify-between px-6 shrink-0 sticky top-0 z-20 transition-colors">
    <!-- Breadcrumb -->
    <div class="flex flex-col justify-center">
        <nav class="text-caption text-slate-gray flex items-center gap-1.5 font-normal">
            <span class="text-ash-gray">/</span>
            <span class="text-ink-black dark:text-paper-white font-medium capitalize">
                {{ request()->route()?->getName() ? str_replace(['.', '-', 'index'], [' / ', ' ', ''], request()->route()->getName()) : 'Dashboard' }}
            </span>
        </nav>
    </div>

    <!-- Right Controls: Search, Theme Toggle, Notifications, Profile -->
    <div class="flex items-center gap-3 sm:gap-4">
        <!-- Search Input -->
        <div class="relative hidden sm:block w-48 lg:w-60">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-ash-gray">
                <x-dynamic-component component="lucide-search" class="w-4 h-4" />
            </span>
            <input
                type="text"
                placeholder="Cari menu & data..."
                class="w-full rounded-input border border-border-gray dark:border-border-gray/20 bg-fog-white dark:bg-[#1a2255] pl-9 pr-3.5 py-1.5 text-body-sm text-ink-black dark:text-paper-white placeholder-ash-gray transition focus:border-primary focus:bg-paper-white dark:focus:bg-[#1a2255] focus:outline-none focus:shadow-focus-ring"
            />
        </div>

        <!-- Theme Toggle & Notification Controls -->
        <div class="flex items-center gap-1.5 text-slate-gray">
            <!-- Dark / Light Mode Toggle Button -->
            <button
                type="button"
                x-data="{
                    darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                    toggleTheme() {
                        this.darkMode = !this.darkMode;
                        if (this.darkMode) {
                            document.documentElement.classList.add('dark');
                            localStorage.setItem('theme', 'dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                            localStorage.setItem('theme', 'light');
                        }
                    }
                }"
                @click="toggleTheme()"
                class="p-2 rounded-input hover:bg-mist-gray dark:hover:bg-paper-white/10 text-slate-gray hover:text-primary dark:hover:text-primary transition"
                :title="darkMode ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'"
            >
                <span x-show="!darkMode">
                    <x-dynamic-component component="lucide-moon" class="w-4 h-4 text-ink-black" />
                </span>
                <span x-show="darkMode" style="display: none;">
                    <x-dynamic-component component="lucide-sun" class="w-4 h-4 text-warning" />
                </span>
            </button>

            <!-- Notification Dropdown -->
            <div>
                <x-dropdown align="right" width="80" content-classes="py-0 bg-paper-white dark:bg-[#111c44] border border-border-gray dark:border-border-gray/10 rounded-card shadow-elevated overflow-hidden w-80 sm:w-96">
                    <x-slot name="trigger">
                        <button type="button" class="p-2 rounded-input hover:bg-mist-gray dark:hover:bg-paper-white/5 hover:text-primary transition relative" title="Notifikasi">
                            <x-dynamic-component component="lucide-bell" class="w-4 h-4" />
                            @if($unreadPersistedCount > 0 || count($computedAlerts) > 0)
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-primary rounded-full ring-2 ring-paper-white dark:ring-[#111c44]"></span>
                            @endif
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-border-gray dark:border-border-gray/10 flex items-center justify-between bg-fog-white/60 dark:bg-paper-white/5">
                            <div class="flex items-center gap-2">
                                <h4 class="text-body-sm font-bold text-ink-black dark:text-paper-white">Notifikasi</h4>
                                @if($unreadPersistedCount > 0)
                                    <span class="text-[10px] font-mono font-bold bg-primary-tint text-primary px-1.5 py-0.5 rounded-full">
                                        {{ $unreadPersistedCount }} Baru
                                    </span>
                                @endif
                            </div>
                            @if($unreadPersistedCount > 0)
                                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                    @csrf
                                    <button type="submit" class="text-caption font-semibold text-primary hover:underline">
                                        Tandai semua dibaca
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="divide-y divide-border-gray/50 dark:divide-border-gray/10 max-h-80 overflow-y-auto">
                            @forelse($computedAlerts as $alert)
                                <a href="{{ $alert['url'] }}" class="p-3.5 flex items-start gap-3 hover:bg-mist-gray/40 dark:hover:bg-paper-white/5 transition">
                                    <div class="w-8 h-8 rounded-card bg-{{ $alert['color'] }}-bg text-{{ $alert['color'] }} flex items-center justify-center shrink-0 mt-0.5 shadow-subtle">
                                        <x-dynamic-component :component="'lucide-' . $alert['icon']" class="w-4 h-4" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-body-sm font-semibold text-ink-black dark:text-paper-white truncate leading-tight">{{ $alert['title'] }}</p>
                                        <p class="text-caption text-slate-gray mt-0.5 line-clamp-2">{{ $alert['message'] }}</p>
                                    </div>
                                </a>
                            @empty
                            @endforelse

                            @forelse($persistedNotifications as $notification)
                                <a href="{{ route('notifications.open', $notification) }}" class="p-3.5 flex items-start gap-3 hover:bg-mist-gray/40 dark:hover:bg-paper-white/5 transition">
                                    <div class="w-8 h-8 rounded-card bg-{{ $notification->data['color'] }}-tint text-{{ $notification->data['color'] }} flex items-center justify-center shrink-0 mt-0.5 shadow-subtle">
                                        <x-dynamic-component :component="'lucide-' . $notification->data['icon']" class="w-4 h-4" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-body-sm font-semibold text-ink-black dark:text-paper-white truncate leading-tight">{{ $notification->data['title'] }}</p>
                                        <p class="text-caption text-slate-gray mt-0.5 line-clamp-2">{{ $notification->data['message'] }}</p>
                                        <span class="text-[10px] text-ash-gray mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                </a>
                            @empty
                                @if(count($computedAlerts) === 0)
                                    <div class="p-6 text-center text-caption text-slate-gray">Tidak ada notifikasi.</div>
                                @endif
                            @endforelse
                        </div>

                        <div class="p-2.5 border-t border-border-gray dark:border-border-gray/10 text-center bg-fog-white/30 dark:bg-paper-white/5">
                            <span class="text-[11px] text-slate-gray font-medium">Sistem Notifikasi Terpusat KelolaIn</span>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Settings Quick Action -->
            <a href="{{ route('profile.edit') }}" class="p-2 rounded-input hover:bg-mist-gray dark:hover:bg-paper-white/5 hover:text-primary transition" title="Pengaturan Akun">
                <x-dynamic-component component="lucide-settings" class="w-4 h-4" />
            </a>
        </div>

        <!-- User Profile Dropdown -->
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="flex items-center gap-2.5 pl-2 py-1 rounded-input hover:bg-mist-gray dark:hover:bg-paper-white/5 transition group">
                    <span class="h-8 w-8 rounded-full bg-primary text-paper-white flex items-center justify-center text-caption font-bold shadow-subtle group-hover:bg-primary-hover">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </span>
                    <span class="hidden md:inline-block text-body-sm font-medium text-ink-black dark:text-paper-white group-hover:text-primary transition">
                        {{ auth()->user()->name ?? 'User' }}
                    </span>
                    <x-dynamic-component component="lucide-chevron-down" class="w-3.5 h-3.5 text-slate-gray" />
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="px-4 py-2 border-b border-border-gray dark:border-border-gray/10">
                    <p class="text-caption font-semibold text-ink-black dark:text-paper-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                    <p class="text-caption text-slate-gray truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>

                <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2">
                    <x-dynamic-component component="lucide-user" class="w-4 h-4" />
                    {{ __('Profil Akun') }}
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
