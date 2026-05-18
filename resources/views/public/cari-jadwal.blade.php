<x-guest-layout>
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-4">
                <a href="{{ route('schedules.public') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Kalender Dosen
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <h2 class="font-black text-2xl text-gray-800 mb-6">Pencarian Ruangan Kosong</h2>
                <form action="{{ route('public.cari_jadwal_kosong') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal</label>
                        <input type="date" name="date" value="{{ $date }}" required class="rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Jam Mulai</label>
                        <input type="time" name="start_time" value="{{ $startTime ?? '07:00' }}" required class="rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Jam Selesai</label>
                        <input type="time" name="end_time" value="{{ $endTime ?? '18:00' }}" required class="rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full">
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition">Cari Ruangan</button>
                    </div>
                </form>
            </div>

            @if($date)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-black text-gray-800 font-bold">
                        Ketersediaan pada {{ \Carbon\Carbon::parse($date)->format('d F Y') }}
                        @if($startTime && $endTime)
                             ({{ $startTime }} - {{ $endTime }})
                        @endif
                    </h3>
                </div>
                <div class="p-6">
                    @if(count($availableSlots) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($availableSlots as $slot)
                                <div class="border border-green-200 bg-green-50 rounded-xl p-4 flex items-center justify-between">
                                    <div>
                                        <div class="font-black text-gray-900 font-bold">{{ $slot['room']->name }}</div>
                                        <div class="text-sm font-medium text-green-700">{{ \Carbon\Carbon::parse($slot['start'])->format('H:i') }} - {{ \Carbon\Carbon::parse($slot['end'])->format('H:i') }}</div>
                                    </div>
                                    <div class="w-3 h-3 rounded-full bg-green-500 shadow-sm shadow-green-200"></div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10 text-gray-500">
                            Tidak ada ruangan kosong yang tersedia pada kriteria waktu ini.
                        </div>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</x-guest-layout>
