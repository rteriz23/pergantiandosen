<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-10 w-auto fill-current text-gray-600" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(Auth::user()->role === 'kaprodi')
                        <x-nav-link :href="route('kaprodi.requests')" :active="request()->routeIs('kaprodi.*')">
                            Permohonan
                        </x-nav-link>
                        <x-nav-link :href="route('kaprodi.calendar')" :active="request()->routeIs('kaprodi.calendar')">
                            Kalender
                        </x-nav-link>
                    @endif
                    @if(Auth::user()->role === 'baa')
                        <x-nav-link :href="route('baa.requests')" :active="request()->routeIs('baa.requests')">
                            Pergantian
                        </x-nav-link>
                        <x-nav-link :href="route('admin.schedule.index')" :active="request()->routeIs('admin.schedule.*')">
                            Jadwal
                        </x-nav-link>
                        <x-nav-link :href="route('baa.settings')" :active="request()->routeIs('baa.settings')">
                            Pengaturan
                        </x-nav-link>
                        <x-nav-link :href="route('baa.periodes.index')" :active="request()->routeIs('baa.periodes.*')">
                            Periode
                        </x-nav-link>
                        <x-nav-link :href="route('kemahasiswaan.settings')" :active="request()->routeIs('kemahasiswaan.*')">
                            Kemahasiswaan
                        </x-nav-link>
                    @endif
                    @if(Auth::user()->role === 'kemahasiswaan')
                        <x-nav-link :href="route('kemahasiswaan.settings')" :active="request()->routeIs('kemahasiswaan.settings')">
                            Pengaturan Batas
                        </x-nav-link>
                        <x-nav-link :href="route('kemahasiswaan.mahasiswas')" :active="request()->routeIs('kemahasiswaan.mahasiswas')">
                            Data Mahasiswa
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown and Notifications -->
            <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-4">
                
                <!-- Notification Bell -->
                <x-dropdown align="right" width="64">
                    <x-slot name="trigger">
                        <button id="notif-bell-btn" class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full focus:outline-none transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span id="notif-count-badge"
                                class="absolute top-1 right-1 inline-flex items-center justify-center w-5 h-5 text-[10px] font-black leading-none text-white bg-red-600 rounded-full transition-all"
                                style="{{ Auth::user()->unreadNotifications->count() > 0 ? '' : 'display:none' }}">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="w-80 max-h-96 overflow-y-auto">
                            <div class="px-4 py-2 font-semibold text-gray-800 border-b border-gray-100 bg-gray-50">Notifikasi</div>
                            @forelse(Auth::user()->unreadNotifications as $notification)
                                <a href="{{ $notification->data['url'] }}?read_notification={{ $notification->id }}" class="block px-4 py-3 hover:bg-indigo-50 border-b border-gray-100 transition-colors">
                                    <div class="text-sm text-gray-800 font-medium">{{ $notification->data['dosen_name'] }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $notification->data['message'] }}</div>
                                    <div class="text-[10px] text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                                </a>
                            @empty
                                <div class="px-4 py-6 text-sm text-gray-500 text-center">Belum ada notifikasi baru.</div>
                            @endforelse
                        </div>
                    </x-slot>
                </x-dropdown>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if(Auth::user()->role == 'baa')
                <x-responsive-nav-link :href="route('baa.periodes.index')" :active="request()->routeIs('baa.periodes.*')">
                    {{ __('Kelola Periode') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

{{-- Real-time notification polling (no Socket.IO needed) --}}
<script>
(function() {
    const badge = document.getElementById('notif-count-badge');
    if (!badge) return;

    async function pollNotifications() {
        try {
            const res  = await fetch('/api/notifications/poll', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            const count = data.unread_count || 0;
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline-flex' : 'none';
        } catch(e) { /* silent fail */ }
    }

    // Poll every 30 seconds
    setInterval(pollNotifications, 30000);
})();
</script>
