<nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-150/80 shadow-sm no-print">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center space-x-8">
                <!-- Brand / Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('schedules.public', ['dosen_id' => request('dosen_id'), 'periode' => request('periode')]) }}" class="flex items-center space-x-2">
                        <span class="text-xl font-black bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 tracking-tight">
                            LPKIA PORTAL
                        </span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex space-x-2">
                    <a href="{{ route('schedules.public', ['dosen_id' => request('dosen_id'), 'periode' => request('periode')]) }}" 
                       class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('schedules.public') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100/50' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }}">
                        📅 Kalender Dosen
                    </a>
                    <a href="{{ route('public.cari_jadwal_kosong', ['dosen_id' => request('dosen_id'), 'date' => request('date')]) }}" 
                       class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('public.cari_jadwal_kosong') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100/50' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }}">
                        🔍 Cari Ruangan Kosong
                    </a>
                    <a href="{{ route('public.jadwal_ruangan', ['date' => request('date')]) }}" 
                       class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('public.jadwal_ruangan') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100/50' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }}">
                        🏫 Cek Jadwal Ruangan
                    </a>
                    <a href="{{ route('public.kalender_akademik') }}" 
                       class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('public.kalender_akademik') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100/50' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }}">
                        🎓 Kalender Akademik
                    </a>
                </div>
            </div>

            <!-- Right side: Authentication / Action -->
            <div class="flex items-center space-x-4">
                @auth
                    <!-- User is logged in -->
                    <div class="hidden sm:flex items-center space-x-3 bg-gray-50 border border-gray-100 rounded-2xl px-4 py-1.5">
                        <span class="text-[10px] font-black text-indigo-700 bg-indigo-50 border border-indigo-100/80 px-2 py-0.5 rounded-md uppercase tracking-wider">
                            {{ Auth::user()->role }}
                        </span>
                        <span class="text-xs font-bold text-gray-700">{{ Auth::user()->name }}</span>
                        
                        <div class="h-4 w-[1px] bg-gray-200"></div>

                        <a href="{{ route('dashboard') }}" class="px-3.5 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-700 transition shadow-sm">
                            Dashboard
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="p-1 text-gray-400 hover:text-red-500 transition-colors" title="Log Out">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </button>
                        </form>
                    </div>
                @else
                    <!-- Guest -->
                    <a href="{{ route('login') }}" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-black rounded-xl transition shadow-lg shadow-indigo-500/20 uppercase tracking-widest hover:-translate-y-0.5 transform">
                        Login Portal Dosen
                    </a>
                @endauth

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center" x-data="{ open: false }">
                    <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none p-2 hover:bg-gray-50 rounded-xl transition">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Mobile Dropdown Menu -->
                    <div x-show="open" @click.away="open = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute top-16 right-4 w-56 bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-gray-150 p-4 space-y-2 z-50">
                        <a href="{{ route('schedules.public', ['dosen_id' => request('dosen_id'), 'periode' => request('periode')]) }}" 
                           class="block px-4 py-2.5 rounded-xl text-sm font-bold text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                            📅 Kalender Dosen
                        </a>
                        <a href="{{ route('public.cari_jadwal_kosong', ['dosen_id' => request('dosen_id'), 'date' => request('date')]) }}" 
                           class="block px-4 py-2.5 rounded-xl text-sm font-bold text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                            🔍 Cari Ruangan Kosong
                        </a>
                        <a href="{{ route('public.jadwal_ruangan', ['date' => request('date')]) }}" 
                           class="block px-4 py-2.5 rounded-xl text-sm font-bold text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                            🏫 Cek Jadwal Ruangan
                        </a>
                        <a href="{{ route('public.kalender_akademik') }}" 
                           class="block px-4 py-2.5 rounded-xl text-sm font-bold text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                            🎓 Kalender Akademik
                        </a>
                        @auth
                            <div class="border-t border-gray-100 pt-2 mt-2">
                                <div class="px-4 py-1.5 text-xs text-gray-400 font-bold uppercase tracking-wider">{{ Auth::user()->role }}</div>
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 rounded-xl text-sm font-bold text-indigo-600 hover:bg-indigo-50 transition">
                                    Dashboard
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-bold text-red-600 hover:bg-red-50 transition">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="border-t border-gray-100 pt-2 mt-2">
                                <a href="{{ route('login') }}" class="block w-full text-center py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition">
                                    Login Portal Dosen
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
