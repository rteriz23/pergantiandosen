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
                    @if(request('dosen_id'))
                        <input type="hidden" name="dosen_id" value="{{ request('dosen_id') }}">
                    @endif
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
                    @if(count($roomsStatus) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($roomsStatus as $status)
                                @if($status['is_available'])
                                    @php
                                        $dosenId = request('dosen_id');
                                        $requestUrl = $dosenId 
                                            ? route('schedules.request_new', [
                                                'dosen_id' => $dosenId,
                                                'date' => $date . ' ' . ($startTime ?? '07:30'),
                                                'room' => $status['room']->name
                                              ])
                                            : '#';
                                    @endphp
                                    @if($dosenId)
                                    <a href="{{ $requestUrl }}" class="border border-green-200 bg-green-50/70 rounded-xl p-4 flex items-center justify-between transition hover:shadow-lg hover:border-green-300 hover:bg-green-100 cursor-pointer group">
                                        <div>
                                            <div class="font-black text-gray-900 font-bold flex items-center group-hover:text-green-950">
                                                {{ $status['room']->name }}
                                                <span class="text-[9px] font-bold bg-green-100 text-green-700 px-1.5 py-0.5 rounded-md ml-2 uppercase">KOSONG</span>
                                            </div>
                                            <div class="text-xs font-semibold text-green-700 mt-1 flex items-center gap-1 group-hover:text-green-800">
                                                <span>✓ Klik untuk Ajukan Ruangan</span>
                                                <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                            </div>
                                        </div>
                                        <div class="w-3.5 h-3.5 rounded-full bg-green-500 shadow-sm shadow-green-200 animate-pulse group-hover:scale-110"></div>
                                    </a>
                                    @else
                                    <div class="border border-green-200 bg-green-50/70 rounded-xl p-4 flex items-center justify-between transition hover:shadow-sm">
                                        <div>
                                            <div class="font-black text-gray-900 font-bold flex items-center">
                                                {{ $status['room']->name }}
                                                <span class="text-[9px] font-bold bg-green-100 text-green-700 px-1.5 py-0.5 rounded-md ml-2 uppercase">KOSONG</span>
                                            </div>
                                            <div class="text-xs font-semibold text-green-700 mt-1">Tersedia untuk Jam Terpilih</div>
                                        </div>
                                        <div class="w-3.5 h-3.5 rounded-full bg-green-500 shadow-sm shadow-green-200 animate-pulse"></div>
                                    </div>
                                    @endif
                                @else
                                    <div class="border border-red-200 bg-red-50/70 rounded-xl p-4 flex items-start justify-between transition hover:shadow-sm">
                                        <div class="pr-2">
                                            <div class="font-black text-gray-900 font-bold flex items-center">
                                                {{ $status['room']->name }}
                                                <span class="text-[9px] font-bold bg-red-100 text-red-700 px-1.5 py-0.5 rounded-md ml-2 uppercase">{{ $status['occupied_by']['type'] }}</span>
                                            </div>
                                            <div class="text-[11px] font-bold text-red-800 mt-1.5">
                                                {{ $status['occupied_by']['waktu'] }}
                                            </div>
                                            <div class="text-xs text-gray-700 mt-1 font-semibold leading-tight">
                                                {{ $status['occupied_by']['dosen'] }}
                                            </div>
                                            <div class="text-[10px] text-gray-500 mt-0.5 font-medium leading-tight">
                                                {{ $status['occupied_by']['mata_kuliah'] }} ({{ $status['occupied_by']['kelas'] }})
                                            </div>
                                        </div>
                                        <div class="w-3.5 h-3.5 rounded-full bg-red-500 shadow-sm shadow-red-200 mt-1 flex-shrink-0"></div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10 text-gray-500">
                            Tidak ada data ruangan ditemukan.
                        </div>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</x-guest-layout>
