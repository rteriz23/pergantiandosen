<x-guest-layout>
    <!-- Background Elements -->
    <div class="fixed inset-0 z-0 bg-[#f8fafc] overflow-hidden">
        <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-300 mix-blend-multiply filter blur-[120px] opacity-70 animate-blob"></div>
        <div class="absolute top-[20%] right-[-10%] w-[50%] h-[50%] rounded-full bg-purple-300 mix-blend-multiply filter blur-[120px] opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-20%] left-[20%] w-[50%] h-[50%] rounded-full bg-pink-300 mix-blend-multiply filter blur-[120px] opacity-70 animate-blob animation-delay-4000"></div>
    </div>

    <div class="relative z-10 min-h-screen py-12 px-4 sm:px-6 lg:px-8 font-sans no-print" 
         x-data="{
             activeRequest: null,
             showModal: false,
             isSuccess: false,
             openRequest(req, isSuccess = false) {
                 this.activeRequest = req;
                 this.isSuccess = isSuccess;
                 this.showModal = true;
             },
             closeModal() {
                 this.showModal = false;
                 this.activeRequest = null;
                 this.isSuccess = false;
             },
             printReceipt() {
                 window.print();
             }
         }"
         x-init="
             @if($successRequest)
                 openRequest({
                     id: '{{ $successRequest->id }}',
                     dosen: '{{ addslashes($successRequest->schedule->dosen->name ?? '-') }}',
                     mata_kuliah: '{{ addslashes($successRequest->schedule->mata_kuliah) }}',
                     kelas: '{{ addslashes($successRequest->schedule->kelas) }}',
                     pertemuan: '{{ $successRequest->schedule->pertemuan }}',
                     waktu_asli: '{{ \Carbon\Carbon::parse($successRequest->schedule->waktu_mulai)->format('l, d M Y (H:i)') }} - {{ \Carbon\Carbon::parse($successRequest->schedule->waktu_selesai)->format('H:i') }}',
                     waktu_usulan: '{{ \Carbon\Carbon::parse($successRequest->waktu_mulai_usulan)->format('l, d M Y (H:i)') }} - {{ \Carbon\Carbon::parse($successRequest->waktu_selesai_usulan)->format('H:i') }}',
                     ruangan: '{{ addslashes($successRequest->ruangan_usulan) }}',
                     alasan: '{{ addslashes($successRequest->alasan) }}',
                     status: '{{ $successRequest->status }}',
                     catatan: '{{ addslashes($successRequest->catatan_kaprodi ?? '-') }}'
                 }, true);
             @endif
         ">
        <div class="max-w-7xl mx-auto">
            
            <!-- Header Section -->
            <div class="text-center mb-12">
                <span class="inline-block py-1 px-3 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-sm font-semibold tracking-wide mb-4 shadow-sm">
                    Portal Akademik Terpadu
                </span>
                <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-700 via-purple-600 to-pink-600 tracking-tight sm:text-6xl mb-4 drop-shadow-sm">
                    Manajemen Jadwal Dosen
                </h1>
                <p class="mt-4 text-xl text-gray-600 max-w-2xl mx-auto font-light">
                    Sistem pemantauan kalender mengajar dan pengajuan jadwal pengganti secara *real-time*.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-4 relative z-20">
                    <a href="{{ route('public.cari_jadwal_kosong') }}" class="inline-flex items-center px-6 py-2.5 bg-indigo-100 text-indigo-700 font-bold rounded-full hover:bg-indigo-200 transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Cari Jadwal Ruangan Kosong
                    </a>
                    <a href="{{ route('public.jadwal_ruangan') }}" class="inline-flex items-center px-6 py-2.5 bg-blue-100 text-blue-700 font-bold rounded-full hover:bg-blue-200 transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Cek Jadwal Ruangan
                    </a>
                    <a href="{{ route('public.kalender_akademik') }}" class="inline-flex items-center px-6 py-2.5 bg-purple-100 text-purple-700 font-bold rounded-full hover:bg-purple-200 transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Cek Kalender Akademik
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-8 max-w-3xl mx-auto bg-white/80 backdrop-blur-md border border-green-200 p-5 rounded-2xl shadow-lg transform transition-all hover:scale-[1.01] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 p-2.5 rounded-full text-green-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-bold text-gray-900">Berhasil!</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ session('success') }}</p>
                        </div>
                    </div>
                    @if($successRequest)
                        <button @click="openRequest({
                            id: '{{ $successRequest->id }}',
                            dosen: '{{ addslashes($successRequest->schedule->dosen->name ?? '-') }}',
                            mata_kuliah: '{{ addslashes($successRequest->schedule->mata_kuliah) }}',
                            kelas: '{{ addslashes($successRequest->schedule->kelas) }}',
                            pertemuan: '{{ $successRequest->schedule->pertemuan }}',
                            waktu_asli: '{{ \Carbon\Carbon::parse($successRequest->schedule->waktu_mulai)->format('l, d M Y (H:i)') }} - {{ \Carbon\Carbon::parse($successRequest->schedule->waktu_selesai)->format('H:i') }}',
                            waktu_usulan: '{{ \Carbon\Carbon::parse($successRequest->waktu_mulai_usulan)->format('l, d M Y (H:i)') }} - {{ \Carbon\Carbon::parse($successRequest->waktu_selesai_usulan)->format('H:i') }}',
                            ruangan: '{{ addslashes($successRequest->ruangan_usulan) }}',
                            alasan: '{{ addslashes($successRequest->alasan) }}',
                            status: '{{ $successRequest->status }}',
                            catatan: '{{ addslashes($successRequest->catatan_kaprodi ?? '-') }}'
                        }, true)" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-xs font-bold text-white rounded-xl shadow-md transition-all transform hover:-translate-y-0.5">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-3a2 2 0 00-2-2H9a2 2 0 00-2 2v3a2 2 0 002 2zm5-14V7a2 2 0 00-2-2H9a2 2 0 00-2 2v3h6z"></path></svg>
                            Lihat & Cetak Bukti
                        </button>
                    @endif
                </div>
            @endif

            <!-- Filter Panel (Glassmorphism) -->
            <div class="bg-white/60 backdrop-blur-xl rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] mb-10 border border-white/40 relative z-20">
                <div class="p-8">
                    <form method="GET" action="{{ route('schedules.public') }}" 
                          x-data="{ 
                              selectedProdi: '',
                              open: false, 
                              search: '', 
                              selectedId: '{{ $selectedDosenId }}', 
                              selectedName: 'Pilih Nama Dosen...' 
                          }" 
                          x-init="
                              @if($selectedDosenId)
                                  @php $d = $dosens->firstWhere('id', $selectedDosenId); @endphp
                                  @if($d) selectedName = '{{ addslashes($d->name) }}'; selectedProdi = '{{ $d->prodi_id }}'; @endif
                              @endif
                          "
                          class="flex flex-col md:flex-row gap-6 items-end">
                        
                        <!-- Filter Prodi -->
                        <div class="w-full md:w-[25%] relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 tracking-wide">PROGRAM STUDI</label>
                            <div class="relative">
                                <select x-model="selectedProdi" @change="selectedId = ''; selectedName = 'Pilih Nama Dosen...'" class="appearance-none w-full bg-white/80 border border-gray-200/80 rounded-2xl shadow-sm pl-5 pr-10 py-4 text-base font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all hover:bg-white cursor-pointer">
                                    <option value="">Semua Prodi</option>
                                    @foreach($prodis as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-indigo-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Search Dosen -->
                        <div class="w-full md:w-[35%] relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 tracking-wide">PENCARIAN DOSEN</label>
                            
                            <div class="relative">
                                <input type="hidden" name="dosen_id" :value="selectedId">
                                <button type="button" @click="open = !open" @click.away="open = false" 
                                        class="relative w-full bg-white/80 border border-gray-200/80 rounded-2xl shadow-sm pl-5 pr-12 py-4 text-left cursor-default focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 hover:bg-white hover:shadow-md">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold mr-3 shadow-inner">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <span class="block truncate text-gray-800 font-semibold text-base" x-text="selectedName"></span>
                                    </div>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <svg class="h-6 w-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </span>
                                </button>

                                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" class="absolute z-50 mt-2 w-full bg-white/95 backdrop-blur-xl shadow-2xl max-h-72 rounded-2xl border border-gray-100 overflow-hidden" style="display: none;">
                                    <div class="sticky top-0 bg-white/90 backdrop-blur-sm px-4 py-3 border-b border-gray-100">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                                            </div>
                                            <input type="text" x-model="search" placeholder="Ketik nama dosen..." class="w-full pl-10 border-none bg-gray-50 rounded-xl py-2.5 text-sm focus:ring-0 focus:bg-gray-100 transition-colors">
                                        </div>
                                    </div>
                                    <ul class="pt-2 pb-2 overflow-y-auto max-h-56">
                                        @foreach($dosens as $dosen)
                                        <li x-show="(selectedProdi === '' || {{ $dosen->taught_prodi_ids->toJson() }}.some(id => id == selectedProdi)) && (search === '' || '{{ strtolower($dosen->name) }}'.includes(search.toLowerCase()))" 
                                            @click="selectedId = '{{ $dosen->id }}'; selectedName = '{{ addslashes($dosen->name) }}'; open = false;"
                                            class="cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-white text-gray-900 transition-colors border-b border-gray-50 last:border-0">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs mr-3">
                                                    {{ substr($dosen->name, 0, 2) }}
                                                </div>
                                                <div>
                                                    <span class="font-semibold block truncate" :class="selectedId === '{{ $dosen->id }}' ? 'text-indigo-600' : 'text-gray-700'">{{ $dosen->name }}</span>
                                                    <span class="text-xs text-gray-400">
                                                        @php
                                                            $taughtProdis = \App\Models\Prodi::whereIn('id', $dosen->taught_prodi_ids)->pluck('name')->toArray();
                                                        @endphp
                                                        {{ implode(', ', $taughtProdis) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Periode -->
                        <div class="w-full md:w-[25%] relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 tracking-wide">PERIODE AJAR</label>
                            <div class="relative">
                                <select name="periode" class="appearance-none w-full bg-white/80 border border-gray-200/80 rounded-2xl shadow-sm pl-5 pr-10 py-4 text-base font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all hover:bg-white cursor-pointer">
                                    <option value="">Semua Periode</option>
                                    @foreach($periodes as $p)
                                        <option value="{{ $p }}" {{ $selectedPeriode == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-indigo-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="w-full md:w-[20%]">
                            <button type="submit" class="w-full flex justify-center items-center py-4 px-6 border border-transparent shadow-lg text-lg font-bold rounded-2xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform transition-all hover:-translate-y-1 hover:shadow-indigo-500/30">
                                <span>Tampilkan</span>
                                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Calendar UI -->
            @if($selectedDosenId)
                <div class="mb-6 p-4 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center shadow-sm">
                    <div class="flex-shrink-0 bg-indigo-500 p-2 rounded-full text-white mr-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-sm text-indigo-800 font-medium">
                        <strong>Tips:</strong> Klik pada kolom jam yang <strong>KOSONG</strong> (Senin-Sabtu) untuk mulai mengajukan jadwal pengganti di waktu tersebut, atau klik jadwal yang sudah ada untuk memindahkannya.
                    </p>
                </div>

                <div class="bg-white/90 backdrop-blur-md shadow-[0_20px_50px_rgb(0,0,0,0.07)] rounded-[2rem] overflow-hidden border border-white p-2 md:p-8 relative z-10 transition-all">
                    <style>
                        .fc-theme-standard td, .fc-theme-standard th { border-color: #f1f5f9; }
                        .fc-col-header-cell-cushion { padding: 12px 0 !important; color: #475569; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.8rem; }
                        .fc-event { border-radius: 8px; border: none !important; padding: 3px; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; }
                        .fc-event:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
                        .fc-timegrid-slot { height: 3em !important; }
                        .fc-toolbar-title { font-weight: 800 !important; color: #1e293b; font-size: 1.5rem !important; }
                        .fc .fc-button-primary { background-color: #fff; color: #4f46e5; border: 1px solid #e0e7ff; font-weight: 600; text-transform: capitalize; border-radius: 0.75rem; padding: 0.5rem 1rem; transition: all 0.2s; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
                        .fc .fc-button-primary:not(:disabled):active, .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: #4f46e5; color: #fff; border-color: #4f46e5; }
                        .fc .fc-button-primary:hover { background-color: #f5f3ff; border-color: #c7d2fe; }
                    </style>
                    <div id="calendar" class="min-h-[700px]"></div>
                </div>
                
                <!-- Legend -->
                <div class="mt-8 flex flex-wrap justify-center gap-6 text-sm font-medium">
                    <div class="flex items-center px-4 py-2 bg-white/60 backdrop-blur-sm rounded-full shadow-sm border border-gray-100">
                        <div class="w-3 h-3 bg-[#3b82f6] rounded-full mr-3 shadow-[0_0_10px_rgba(59,130,246,0.6)]"></div>
                        <span class="text-gray-700">Jadwal Reguler</span>
                    </div>
                    <div class="flex items-center px-4 py-2 bg-white/60 backdrop-blur-sm rounded-full shadow-sm border border-gray-100">
                        <div class="w-3 h-3 bg-[#eab308] rounded-full mr-3 shadow-[0_0_10px_rgba(234,179,8,0.6)]"></div>
                        <span class="text-gray-700">Sedang Diajukan Pergantian</span>
                    </div>
                </div>

                <!-- History of Requests Section -->
                <div class="mt-12 bg-white/60 backdrop-blur-xl rounded-[2rem] shadow-xl border border-white/40 overflow-hidden relative z-20">
                    <div class="p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="bg-indigo-100 text-indigo-700 p-2.5 rounded-2xl">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Riwayat Pengajuan Pergantian</h2>
                            </div>
                            <span class="bg-indigo-100 text-indigo-800 text-xs font-black px-3.5 py-1.5 rounded-full uppercase tracking-wider">
                                {{ $historyRequests->count() }} Pengajuan
                            </span>
                        </div>

                        @if($historyRequests->isEmpty())
                            <div class="text-center py-10 bg-white/30 rounded-2xl border border-dashed border-gray-205">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-gray-500 font-medium">Belum ada riwayat pengajuan pergantian jadwal untuk dosen ini.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white/40">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-widest">
                                            <th class="py-4 px-6">Mata Kuliah & Kelas</th>
                                            <th class="py-4 px-6">Jadwal Asli</th>
                                            <th class="py-4 px-6">Usulan Baru</th>
                                            <th class="py-4 px-6">Ruangan</th>
                                            <th class="py-4 px-6 text-center">Status</th>
                                            <th class="py-4 px-6 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100/50 text-sm font-semibold text-gray-700">
                                        @foreach($historyRequests as $req)
                                            <tr class="hover:bg-white/40 transition-colors">
                                                <td class="py-4 px-6">
                                                    <div class="font-bold text-gray-900">{{ $req->schedule->mata_kuliah }}</div>
                                                    <div class="text-xs text-gray-400 mt-0.5">Kelas {{ $req->schedule->kelas }} • Pertemuan {{ $req->schedule->pertemuan }}</div>
                                                </td>
                                                <td class="py-4 px-6 text-xs text-gray-500">
                                                    {{ \Carbon\Carbon::parse($req->schedule->waktu_mulai)->format('l, d M Y') }}
                                                    <br>
                                                    <span class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($req->schedule->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($req->schedule->waktu_selesai)->format('H:i') }}</span>
                                                </td>
                                                <td class="py-4 px-6 text-xs text-purple-950">
                                                    {{ \Carbon\Carbon::parse($req->waktu_mulai_usulan)->format('l, d M Y') }}
                                                    <br>
                                                    <span class="font-extrabold text-purple-700">{{ \Carbon\Carbon::parse($req->waktu_mulai_usulan)->format('H:i') }} - {{ \Carbon\Carbon::parse($req->waktu_selesai_usulan)->format('H:i') }}</span>
                                                </td>
                                                <td class="py-4 px-6 text-gray-900">{{ $req->ruangan_usulan }}</td>
                                                <td class="py-4 px-6 text-center">
                                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide"
                                                          :class="{
                                                              'bg-yellow-105 text-yellow-800 border border-yellow-200': '{{ $req->status }}' === 'Pending',
                                                              'bg-green-105 text-green-800 border border-green-200': '{{ $req->status }}' === 'Disetujui',
                                                              'bg-red-105 text-red-800 border border-red-200': '{{ $req->status }}' === 'Ditolak'
                                                          }">{{ $req->status }}</span>
                                                </td>
                                                <td class="py-4 px-6 text-right">
                                                    <button @click="openRequest({
                                                        id: '{{ $req->id }}',
                                                        dosen: '{{ addslashes($req->schedule->dosen->name ?? '-') }}',
                                                        mata_kuliah: '{{ addslashes($req->schedule->mata_kuliah) }}',
                                                        kelas: '{{ addslashes($req->schedule->kelas) }}',
                                                        pertemuan: '{{ $req->schedule->pertemuan }}',
                                                        waktu_asli: '{{ \Carbon\Carbon::parse($req->schedule->waktu_mulai)->format('l, d M Y (H:i)') }} - {{ \Carbon\Carbon::parse($req->schedule->waktu_selesai)->format('H:i') }}',
                                                        waktu_usulan: '{{ \Carbon\Carbon::parse($req->waktu_mulai_usulan)->format('l, d M Y (H:i)') }} - {{ \Carbon\Carbon::parse($req->waktu_selesai_usulan)->format('H:i') }}',
                                                        ruangan: '{{ addslashes($req->ruangan_usulan) }}',
                                                        alasan: '{{ addslashes($req->alasan) }}',
                                                        status: '{{ $req->status }}',
                                                        catatan: '{{ addslashes($req->catatan_kaprodi ?? '-') }}'
                                                    })" 
                                                    class="inline-flex items-center px-4 py-2 border border-gray-200 hover:border-indigo-205 hover:bg-indigo-50 text-xs font-bold rounded-xl text-indigo-600 transition-all transform hover:scale-[1.03]">
                                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                        Detail & Cetak
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center py-24 bg-white/40 backdrop-blur-xl rounded-[2rem] shadow-sm border border-white/50 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-white/20"></div>
                    <div class="relative flex flex-col items-center">
                        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-[0_10px_40px_-10px_rgba(79,70,229,0.3)] mb-6 group-hover:scale-110 transition-transform duration-500">
                            <svg class="h-10 w-10 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Pilih Dosen Terlebih Dahulu</h3>
                        <p class="text-gray-500 max-w-md mx-auto">Silakan pilih nama dosen dan rentang periode pada panel di atas untuk menampilkan kalender jadwal secara interaktif.</p>
                    </div>
                </div>
            @endif

        </div>
    </div>

    @if($selectedDosenId)
    <!-- FullCalendar Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', async function() {
        var calendarEl = document.getElementById('calendar');
        
        // Fetch events first to determine initialDate
        const response = await fetch('/api/schedules?dosen_id={{ $selectedDosenId }}&periode={{ $selectedPeriode }}');
        const eventsData = await response.json();
        
        let initialDate = new Date(); // default today
        if (eventsData.length > 0) {
            // Find the earliest date
            const earliest = eventsData.reduce((min, p) => p.start < min ? p.start : min, eventsData[0].start);
            initialDate = earliest.split(' ')[0]; // get YYYY-MM-DD part
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'timeGridWeek',
          initialDate: initialDate,
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
          },
          slotMinTime: '07:00:00',
          slotMaxTime: '22:00:00',
          hiddenDays: [0], // Hide Sunday
          allDaySlot: false,
          nowIndicator: true,
          selectable: true,
          dateClick: function(info) {
              if (confirm('Waktu ini (' + info.dateStr + ') kosong. Apakah Anda ingin mengajukan jadwal pengganti di jam ini?\n\nSilakan pilih jadwal yang ingin Anda pindahkan di halaman berikutnya.')) {
                  // Redirect to a general request page with pre-filled date/time
                  window.location.href = '/schedules/request/new?date=' + encodeURIComponent(info.dateStr) + '&dosen_id={{ $selectedDosenId }}';
              }
          },
          events: eventsData,
          eventClick: function(info) {
             if (!info.event.extendedProps.has_pending_request && info.event.extendedProps.status === 'Terjadwal') {
                 // Add subtle click animation before redirect
                 info.el.style.transform = 'scale(0.95)';
                 setTimeout(() => {
                     window.location.href = '/schedules/request/' + info.event.id;
                 }, 150);
             } else {
                 alert('Jadwal ini tidak dapat diajukan pergantian lagi (Status: ' + info.event.extendedProps.status + ').');
             }
          },
          eventContent: function(arg) {
            let innerHtml = `
                <div class="h-full w-full flex flex-col justify-center px-2 shadow-sm rounded-md" style="background: linear-gradient(135deg, ${arg.event.backgroundColor}cc, ${arg.event.backgroundColor});">
                    <div class="font-bold text-[11px] leading-tight text-white mb-1 drop-shadow-sm">${arg.event.title}</div>
                    <div class="text-[10px] text-white/90 font-medium">Pert: ${arg.event.extendedProps.pertemuan}</div>
                </div>
            `;
            let italicEl = document.createElement('div');
            italicEl.className = 'w-full h-full';
            italicEl.innerHTML = innerHtml;
            let arrayOfDomNodes = [ italicEl ];
            return { domNodes: arrayOfDomNodes }
          }
        });
        calendar.render();
      });
    </script>
    @endif
    
        <!-- Elegant Glassmorphism Modal for Request Details & Printing -->
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-md print-modal-wrapper"
             style="display: none;">
            
            <div class="bg-white/90 backdrop-blur-xl rounded-[2rem] shadow-2xl border border-white/50 max-w-2xl w-full overflow-hidden transform transition-all relative"
                 @click.away="closeModal()" id="printable-receipt-modal">
                
                <!-- Printable-only Header -->
                <div class="hidden print:block text-center border-b-2 border-dashed border-gray-300 pb-6 mb-6">
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">LPKIA ACADEMIC PORTAL</h2>
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mt-1">Bukti Pengajuan Pergantian Jadwal</p>
                    <div class="text-xs text-gray-400 mt-2">Dicetak pada: {{ date('d M Y H:i:s') }}</div>
                </div>

                <!-- Modal Top Accent Gradient (hidden on print) -->
                <div class="h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 w-full print:hidden"></div>
                
                <!-- Close Button (hidden on print) -->
                <button @click="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-full hover:bg-gray-100 print:hidden" title="Tutup">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                <div class="p-8 md:p-10">
                    <!-- SUCCESS STATE HEADER (Visible on screen when isSuccess is true) -->
                    <div x-show="isSuccess" class="text-center mb-8 print:hidden">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mb-4 shadow-lg shadow-emerald-100/50 animate-bounce">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500 tracking-tight mb-2">
                            Permohonan Berhasil Diajukan!
                        </h3>
                        <p class="text-sm font-semibold text-gray-500 max-w-md mx-auto">
                            Permohonan pergantian jadwal Anda telah dikirim dan sedang menunggu verifikasi dari Kaprodi dan BAA.
                        </p>
                    </div>

                    <!-- STANDARD STATE HEADER (Visible on screen when isSuccess is false) -->
                    <div x-show="!isSuccess" class="flex items-center space-x-3 mb-6 print:hidden">
                        <div class="bg-indigo-100 text-indigo-700 p-2.5 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-black text-gray-900">Detail Permohonan</h3>
                    </div>

                    <!-- Receipt Info Cards -->
                    <div class="space-y-6">
                        <!-- Dosen & Mata Kuliah -->
                        <div class="bg-indigo-50/40 border border-indigo-100/50 p-5 rounded-2xl print:bg-white print:border-gray-200">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest">Dosen Pengaju</span>
                                    <span class="text-base font-bold text-gray-800" x-text="activeRequest?.dosen"></span>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest">Status Pengajuan</span>
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold mt-1 uppercase tracking-wide"
                                          :class="{
                                              'bg-yellow-100 text-yellow-800 border border-yellow-200': activeRequest?.status === 'Pending',
                                              'bg-green-100 text-green-800 border border-green-200': activeRequest?.status === 'Disetujui',
                                              'bg-red-100 text-red-800 border border-red-200': activeRequest?.status === 'Ditolak'
                                          }"
                                          x-text="activeRequest?.status"></span>
                                </div>
                            </div>
                            <div class="mt-4 border-t border-indigo-100/30 pt-4 print:border-gray-200">
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest">Mata Kuliah & Kelas</span>
                                <span class="text-lg font-black text-indigo-950 print:text-black" x-text="activeRequest ? activeRequest.mata_kuliah + ' (' + activeRequest.kelas + ') - Pertemuan ' + activeRequest.pertemuan : ''"></span>
                            </div>
                        </div>

                        <!-- Schedule Shift Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Waktu Asli -->
                            <div class="border border-gray-150 p-5 rounded-2xl bg-gray-50/30 print:bg-white">
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5">Jadwal Kuliah Asli</span>
                                <div class="flex items-center text-sm font-semibold text-gray-700">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span x-text="activeRequest?.waktu_asli"></span>
                                </div>
                            </div>
                            <!-- Waktu Usulan -->
                            <div class="border border-purple-150 p-5 rounded-2xl bg-purple-50/20 print:bg-white">
                                <span class="block text-xs font-bold text-purple-600 uppercase tracking-widest mb-1.5 print:text-gray-400">Usulan Jadwal Baru</span>
                                <div class="flex items-center text-sm font-extrabold text-purple-950 print:text-black">
                                    <svg class="w-4 h-4 mr-2 text-purple-500 print:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span x-text="activeRequest?.waktu_usulan"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Ruangan & Alasan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border border-gray-155 p-5 rounded-2xl bg-gray-50/30 print:bg-white">
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Ruangan Usulan</span>
                                <span class="text-base font-bold text-gray-800" x-text="activeRequest?.ruangan"></span>
                            </div>
                            <div class="border border-gray-155 p-5 rounded-2xl bg-gray-50/30 print:bg-white">
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Alasan Pergantian</span>
                                <span class="text-sm font-medium text-gray-600 italic" x-text="activeRequest?.alasan"></span>
                            </div>
                        </div>

                        <!-- Catatan Kaprodi (if rejected/has note) -->
                        <div x-show="activeRequest?.catatan && activeRequest?.catatan !== '-'" class="border border-red-155 p-5 rounded-2xl bg-red-50/20 print:bg-white">
                            <span class="block text-xs font-bold text-red-500 uppercase tracking-widest mb-1 print:text-gray-400">Catatan Kaprodi / BAA</span>
                            <span class="text-sm font-semibold text-red-950 print:text-black" x-text="activeRequest?.catatan"></span>
                        </div>
                    </div>

                    <!-- Printable-only Footer -->
                    <div class="hidden print:block mt-12 border-t border-dashed border-gray-300 pt-6">
                        <div class="flex justify-between text-xs text-gray-400">
                            <span>Sistem Pergantian Jadwal Dosen</span>
                            <span>LPKIA © {{ date('Y') }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons (hidden on print) -->
                    <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-100 print:hidden">
                        <button @click="closeModal()" class="px-6 py-3 border border-gray-200 hover:bg-gray-50 rounded-xl font-bold text-gray-600 transition-colors" x-text="isSuccess ? 'Selesai' : 'Tutup'">
                        </button>
                        <button @click="printReceipt()" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-xl font-bold text-white shadow-lg shadow-indigo-500/20 flex items-center transition-all transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-3a2 2 0 00-2-2H9a2 2 0 00-2 2v3a2 2 0 002 2zm5-14V7a2 2 0 00-2-2H9a2 2 0 00-2 2v3h6z"></path></svg>
                            Cetak Bukti
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }

        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .no-print, .print\:hidden {
                display: none !important;
            }
            .print-modal-wrapper {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                height: auto !important;
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                visibility: visible !important;
                overflow: visible !important;
                z-index: 99999 !important;
            }
            #printable-receipt-modal {
                position: relative !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                border: none !important;
                box-shadow: none !important;
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                visibility: visible !important;
            }
            .print\:block {
                display: block !important;
            }
            .print\:hidden {
                display: none !important;
            }
        }
    </style>
</x-guest-layout>
