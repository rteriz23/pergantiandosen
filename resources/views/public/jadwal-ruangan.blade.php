<x-guest-layout>
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-4">
                <a href="{{ route('schedules.public') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Kalender Dosen
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <h2 class="font-black text-2xl text-gray-800 mb-6">Cek Jadwal Ruangan</h2>
                <form action="{{ route('public.jadwal_ruangan') }}" method="GET" class="flex gap-4 items-end">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Pilih Tanggal</label>
                        <input type="date" name="date" value="{{ $date }}" class="rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition">Cek Ruangan</button>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-black text-gray-800">Ruangan Terpakai pada {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="py-4 px-6 font-bold text-gray-600 text-sm">Ruangan</th>
                                <th class="py-4 px-6 font-bold text-gray-600 text-sm">Waktu</th>
                                <th class="py-4 px-6 font-bold text-gray-600 text-sm">Mata Kuliah / Kelas</th>
                                <th class="py-4 px-6 font-bold text-gray-600 text-sm">Dosen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($approvedRequests as $req)
                            <tr class="hover:bg-gray-50">
                                <td class="py-4 px-6 font-bold text-gray-900">{{ $req->room->name ?? $req->ruangan_usulan ?? '-' }} <span class="text-[10px] font-bold bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded-md ml-1">PENGGANTI</span></td>
                                <td class="py-4 px-6 font-medium text-gray-700">
                                    {{ \Carbon\Carbon::parse($req->waktu_mulai_usulan)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($req->waktu_selesai_usulan)->format('H:i') }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-gray-800">{{ $req->schedule->mata_kuliah }}</div>
                                    <div class="text-sm text-gray-500">Kelas {{ $req->schedule->kelas }}</div>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-700">{{ $req->pengaju_display_name ?? $req->pengaju->name }}</td>
                            </tr>
                            @endforeach

                            @foreach($regularSchedules as $sched)
                            <tr class="hover:bg-gray-50">
                                <td class="py-4 px-6 font-bold text-gray-900">{{ $sched->room->name ?? 'TBA' }} <span class="text-[10px] font-bold bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-md ml-1">REGULER</span></td>
                                <td class="py-4 px-6 font-medium text-gray-700">
                                    {{ \Carbon\Carbon::parse($sched->waktu_mulai)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($sched->waktu_selesai)->format('H:i') }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-gray-800">{{ $sched->mata_kuliah }}</div>
                                    <div class="text-sm text-gray-500">Kelas {{ $sched->kelas }}</div>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-700">{{ $sched->dosen->name ?? '-' }}</td>
                            </tr>
                            @endforeach

                            @if($approvedRequests->isEmpty() && $regularSchedules->isEmpty())
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-500">Belum ada jadwal kelas atau ruangan yang terpakai pada tanggal ini.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-guest-layout>
