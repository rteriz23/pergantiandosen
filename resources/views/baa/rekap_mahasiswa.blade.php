<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rekap Presensi Mahasiswa D3 & S1</h2>
                <p class="text-sm text-gray-500 mt-0.5">Data jadwal perkuliahan mahasiswa — Mata Kuliah, Dosen, Jam, & Ruangan</p>
            </div>
            <a href="{{ route('baa.rekap_mahasiswa.export', request()->query()) }}"
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
                <form action="{{ route('baa.rekap_mahasiswa') }}" method="GET"
                      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Cari Nama / NIM / Kelas</label>
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                               placeholder="Ketik kata kunci..."
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Periode</label>
                        <select name="periode" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 text-sm">
                            <option value="">— Semua Periode —</option>
                            @foreach($periodes as $p)
                                <option value="{{ $p }}" {{ ($periode ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Program Studi</label>
                        <select name="prodi_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 text-sm">
                            <option value="">— Semua Prodi —</option>
                            @foreach($prodis as $prodi)
                                <option value="{{ $prodi->id }}" {{ ($prodiId ?? '') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                            Terapkan
                        </button>
                        @if($search || $periode || $prodiId)
                            <a href="{{ route('baa.rekap_mahasiswa') }}"
                               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 text-sm font-medium transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Summary Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <div class="text-2xl font-black text-indigo-600">{{ $mahasiswas->total() }}</div>
                    <div class="text-xs font-bold uppercase text-gray-500 mt-1">Total Mahasiswa</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <div class="text-2xl font-black text-red-600">
                        {{ $mahasiswas->getCollection()->where('status_mengulang', true)->count() }}
                    </div>
                    <div class="text-xs font-bold uppercase text-gray-500 mt-1">Status Mengulang</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <div class="text-2xl font-black text-green-600">
                        {{ $mahasiswas->getCollection()->where('status_mengulang', false)->count() }}
                    </div>
                    <div class="text-xs font-bold uppercase text-gray-500 mt-1">Status Reguler</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <div class="text-2xl font-black text-purple-600">
                        {{ $mahasiswas->getCollection()->sum(fn($m) => count($m->jadwal_list)) }}
                    </div>
                    <div class="text-xs font-bold uppercase text-gray-500 mt-1">Total Jadwal</div>
                </div>
            </div>

            {{-- Mahasiswa Cards --}}
            @forelse($mahasiswas as $mhs)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

                {{-- Student Header --}}
                <div class="flex items-start justify-between px-6 py-4 border-b border-gray-100
                            {{ $mhs->status_mengulang ? 'bg-red-50' : 'bg-indigo-50' }}">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-black text-white
                                    {{ $mhs->status_mengulang ? 'bg-red-500' : 'bg-indigo-500' }}">
                            {{ strtoupper(substr($mhs->nama, 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-base">{{ $mhs->nama }}</div>
                            <div class="flex items-center gap-3 mt-0.5">
                                <span class="text-xs text-gray-500 font-semibold">NIM: {{ $mhs->nim }}</span>
                                <span class="text-xs text-gray-500">•</span>
                                <span class="text-xs text-gray-500">Kelas: <strong>{{ $mhs->kelas ?? '-' }}</strong></span>
                                <span class="text-xs text-gray-500">•</span>
                                <span class="text-xs text-gray-500">{{ $mhs->prodi->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($mhs->status_mengulang)
                            <span class="px-3 py-1.5 rounded-full bg-red-100 text-red-700 text-xs font-black uppercase tracking-wide">
                                ⚠ Mengulang
                            </span>
                        @else
                            <span class="px-3 py-1.5 rounded-full bg-green-100 text-green-700 text-xs font-black uppercase tracking-wide">
                                ✓ Reguler
                            </span>
                        @endif
                        <span class="px-3 py-1.5 rounded-full bg-white/80 text-gray-600 text-xs font-bold border border-gray-200">
                            {{ count($mhs->jadwal_list) }} Mata Kuliah
                        </span>
                    </div>
                </div>

                {{-- Schedule Table --}}
                @if(count($mhs->jadwal_list) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs font-bold uppercase text-gray-500 border-b border-gray-100">
                                <th class="px-6 py-3 text-left bg-gray-50/60">Mata Kuliah</th>
                                <th class="px-4 py-3 text-left bg-gray-50/60">Dosen Pengampu</th>
                                <th class="px-4 py-3 text-left bg-gray-50/60">Hari & Jam</th>
                                <th class="px-4 py-3 text-left bg-gray-50/60">Ruangan</th>
                                <th class="px-4 py-3 text-left bg-gray-50/60">Pertemuan</th>
                                <th class="px-4 py-3 text-left bg-gray-50/60">Periode</th>
                                <th class="px-4 py-3 text-left bg-gray-50/60">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($mhs->jadwal_list as $j)
                            <tr class="hover:bg-indigo-50/30 transition-colors">
                                <td class="px-6 py-3 font-semibold text-gray-900">
                                    {{ $j['mata_kuliah'] }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-purple-400 to-indigo-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($j['dosen_nama'], 0, 1)) }}
                                        </div>
                                        <span class="text-sm">{{ $j['dosen_nama'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-900 font-semibold">{{ $j['hari'] }}</div>
                                    <div class="text-gray-500 text-xs">{{ $j['jam'] }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-800 font-medium">{{ $j['ruangan'] }}</div>
                                    @if($j['ruangan_tipe'] && $j['ruangan_tipe'] !== '-')
                                    <div class="text-gray-400 text-xs capitalize">{{ $j['ruangan_tipe'] }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-gray-700">
                                    <span class="px-2 py-1 rounded bg-gray-100 text-xs font-bold">{{ $j['pertemuan'] ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $j['periode'] ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($j['status'] === 'Terjadwal')
                                        <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">Terjadwal</span>
                                    @elseif($j['status'] === 'Selesai')
                                        <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">Selesai</span>
                                    @elseif($j['status'] === 'Diganti')
                                        <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">Diganti</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">{{ $j['status'] }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-6 py-8 text-center text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm">Belum ada jadwal ditemukan untuk kelas <strong>{{ $mhs->kelas ?? '(tidak ada kelas)' }}</strong>
                        @if($periode) pada periode <strong>{{ $periode }}</strong>@endif.
                    </p>
                    @if(!$mhs->kelas)
                    <p class="text-xs text-amber-600 mt-1">⚠ Data kelas mahasiswa ini belum diisi. Silakan update di master mahasiswa.</p>
                    @endif
                </div>
                @endif

            </div>
            @empty
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-16 text-center text-gray-400">
                <svg class="w-14 h-14 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-lg font-semibold">Tidak ada data mahasiswa ditemukan.</p>
                <p class="text-sm mt-1">Coba ubah filter pencarian atau import data mahasiswa terlebih dahulu.</p>
                <a href="{{ route('admin.mahasiswa.index') }}" class="mt-4 inline-block px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                    Kelola Master Mahasiswa
                </a>
            </div>
            @endforelse

            {{-- Pagination --}}
            @if($mahasiswas->hasPages())
            <div class="mt-2">
                {{ $mahasiswas->links() }}
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
