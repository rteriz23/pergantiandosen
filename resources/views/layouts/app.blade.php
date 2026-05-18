<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap">

        <!-- Scripts & Styles (CDN for rapid development without Vite/Mix) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
            body { font-family: 'Outfit', sans-serif; }
            .glass {
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.3);
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 flex">
            
            @if(Auth::check() && Auth::user()->role === 'admin')
                <!-- Admin Sidebar -->
                <aside class="w-64 bg-white shadow-md hidden sm:block z-10 flex-shrink-0 border-r border-gray-200">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-xl font-bold text-gray-800">Master Data</h2>
                    </div>
                    <nav class="p-4 space-y-2">
                        <a href="{{ route('admin.dosen.index') }}" class="block px-4 py-2.5 rounded-lg {{ request()->routeIs('admin.dosen.*') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-gray-700 hover:text-blue-600 transition' }}">Dosen</a>
                        <a href="{{ route('admin.mahasiswa.index') }}" class="block px-4 py-2.5 rounded-lg {{ request()->routeIs('admin.mahasiswa.*') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-gray-700 hover:text-blue-600 transition' }}">Mahasiswa</a>
                        <a href="{{ route('admin.matakuliah.index') }}" class="block px-4 py-2.5 rounded-lg {{ request()->routeIs('admin.matakuliah.*') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-gray-700 hover:text-blue-600 transition' }}">Mata Kuliah</a>
                        <a href="{{ route('admin.kelas.index') }}" class="block px-4 py-2.5 rounded-lg {{ request()->routeIs('admin.kelas.*') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-gray-700 hover:text-blue-600 transition' }}">Kelas</a>
                        <a href="{{ route('admin.room.index') }}" class="block px-4 py-2.5 rounded-lg {{ request()->routeIs('admin.room.*') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-gray-700 hover:text-blue-600 transition' }}">Ruangan</a>
                        <a href="{{ route('admin.schedule.index') }}" class="block px-4 py-2.5 rounded-lg {{ request()->routeIs('admin.schedule.*') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-gray-700 hover:text-blue-600 transition' }}">Jadwal</a>
                        <a href="{{ route('baa.periodes.index') }}" class="block px-4 py-2.5 rounded-lg {{ request()->routeIs('baa.periodes.*') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-gray-700 hover:text-blue-600 transition' }}">Periode</a>
                        <a href="{{ route('admin.kalender.index') }}" class="block px-4 py-2.5 rounded-lg {{ request()->routeIs('admin.kalender.*') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-gray-700 hover:text-blue-600 transition' }}">Kalender Akademik</a>
                    </nav>
                </aside>
            @endif

            <div class="flex-1 flex flex-col min-w-0">
                @include('layouts.navigation')

                <!-- Page Heading -->
                @if(isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="flex-1 overflow-x-hidden">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
