<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Pergantian Jadwal Kuliah</h2>
                <p class="text-sm text-gray-500 mt-0.5">Monitoring dan rekapitulasi data pergantian jadwal PBM dosen asli & pengganti</p>
            </div>
            <a href="{{ route('baa.laporan_pergantian.export', request()->query()) }}"
               class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export CSV
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filter Card --}}
            <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100">
                <form action="{{ route('baa.laporan_pergantian') }}" method="GET"
                      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Cari MK / Kelas / Nama</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Ketik kata kunci..."
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Dosen Asli / Pengganti</label>
                        <select name="dosen_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 text-sm">
                            <option value="">— Semua Dosen —</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ request('dosen_id') == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Status Pengajuan</label>
                        <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 text-sm">
                            <option value="">— Semua Status —</option>
                            <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Disetujui" {{ request('status') === 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="Ditolak" {{ request('status') === 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Dari Tanggal</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Sampai Tanggal</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 text-sm">
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                            Terapkan
                        </button>
                        @if(request()->anyFilled(['search', 'dosen_id', 'status', 'start_date', 'end_date']))
                            <a href="{{ route('baa.laporan_pergantian') }}"
                               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 text-sm font-medium transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs font-bold uppercase text-gray-500 border-b border-gray-100 bg-gray-50/60">
                                <th class="px-6 py-4 text-left">Tanggal Diajukan</th>
                                <th class="px-4 py-4 text-left">Dosen Asli</th>
                                <th class="px-4 py-4 text-left">Dosen Pengganti</th>
                                <th class="px-4 py-4 text-left">Mata Kuliah & Kelas</th>
                                <th class="px-4 py-4 text-left">Waktu Semula</th>
                                <th class="px-4 py-4 text-left">Waktu Usulan</th>
                                <th class="px-4 py-4 text-left">Ruangan</th>
                                <th class="px-4 py-4 text-left">Pengaju</th>
                                <th class="px-4 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($requests as $r)
                            <tr class="hover:bg-indigo-50/10 transition-colors">
                                <td class="px-6 py-4 text-gray-500 font-medium text-xs">
                                    {{ $r->created_at ? $r->created_at->format('d M Y H:i') : '-' }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-gray-900 text-sm">{{ $r->schedule->dosen->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $r->schedule->prodi->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-4">
                                    @if($r->dosenPengganti)
                                        <div class="font-bold text-indigo-700 text-sm">{{ $r->dosenPengganti->name }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">NIDN: {{ $r->dosenPengganti->nidn ?? '-' }}</div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Tidak Ada (Kuliah Mandiri)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-gray-900 text-sm">{{ $r->schedule->mata_kuliah ?? '-' }}</div>
                                    <div class="text-xs font-bold text-gray-500 mt-0.5">Kelas: {{ $r->schedule->kelas ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-4 text-xs">
                                    @if($r->schedule)
                                        <div class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($r->schedule->waktu_mulai)->isoFormat('dddd, d M Y') }}</div>
                                        <div class="text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($r->schedule->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->schedule->waktu_selesai)->format('H:i') }}</div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-xs">
                                    <div class="font-bold text-indigo-900">{{ \Carbon\Carbon::parse($r->waktu_mulai_usulan)->isoFormat('dddd, d M Y') }}</div>
                                    <div class="text-indigo-600 font-semibold mt-0.5">{{ \Carbon\Carbon::parse($r->waktu_mulai_usulan)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->waktu_selesai_usulan)->format('H:i') }}</div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-gray-800 text-xs bg-gray-100 px-2 py-1 rounded inline-block">
                                        {{ $r->room->name ?? ($r->ruangan_usulan ?? '-') }}
                                    </div>
                                    @if($r->is_online)
                                        <div class="mt-1"><span class="text-[9px] font-bold bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full uppercase tracking-wider">DARING</span></div>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-gray-800 text-xs">{{ $r->pengaju_nama ?? ($r->pengaju->name ?? '-') }}</div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $r->pengaju_nim_nidn }}</div>
                                    <div class="mt-1">
                                        @if($r->pengaju_type === 'dosen')
                                            <span class="text-[8px] font-black bg-blue-50 text-blue-600 border border-blue-200 px-1 py-0.2 rounded uppercase">DOSEN</span>
                                        @else
                                            <span class="text-[8px] font-black bg-red-50 text-red-600 border border-red-200 px-1 py-0.2 rounded uppercase">MAHASISWA</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($r->status === 'Pending')
                                        <span class="px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-black tracking-wide uppercase">Pending</span>
                                    @elseif($r->status === 'Disetujui')
                                        <span class="px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-black tracking-wide uppercase">Disetujui</span>
                                    @elseif($r->status === 'Ditolak')
                                        <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-800 text-xs font-black tracking-wide uppercase">Ditolak</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-800 text-xs font-black tracking-wide uppercase">{{ $r->status }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('schedules.request', $r->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs transition">Detail</a>
                                        <span class="text-gray-300">|</span>
                                        <a href="{{ route('schedules.request_print', $r->id) }}" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-bold text-xs transition flex items-center gap-0.5">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            Cetak
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    <p class="text-base font-semibold">Tidak ada data laporan pergantian jadwal ditemukan.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            @if($requests->hasPages())
            <div class="mt-4">
                {{ $requests->links() }}
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
