<x-app-layout>
<x-slot name="header">
    <div class="flex items-center justify-between">
        <h2 class="font-bold text-xl text-gray-800">BAA — Manajemen Pergantian Jadwal & Honor Dosen</h2>
        <div class="flex gap-2">
            <a href="{{ route('baa.settings') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-xl transition-all">⚙ Pengaturan</a>
            <a href="{{ route('baa.honor.export') }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-all">⬇ Export Honor CSV</a>
        </div>
    </div>
</x-slot>

<div class="py-8" x-data="baaDashboard()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-2xl">{{ session('success') }}</div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $totalPending  = $requests->where('status','Pending')->count();
                $totalApproved = $requests->where('status','Disetujui')->count();
                $totalOnline   = $requests->where('is_online', true)->count();
                $totalOffline  = $requests->where('is_online', false)->where('status','Disetujui')->count();
            @endphp
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 text-center">
                <div class="text-3xl font-black text-yellow-700">{{ $totalPending }}</div>
                <div class="text-xs font-bold text-yellow-600 uppercase tracking-widest mt-1">Menunggu</div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-2xl p-4 text-center">
                <div class="text-3xl font-black text-green-700">{{ $totalApproved }}</div>
                <div class="text-xs font-bold text-green-600 uppercase tracking-widest mt-1">Disetujui</div>
            </div>
            <div class="bg-cyan-50 border border-cyan-200 rounded-2xl p-4 text-center">
                <div class="text-3xl font-black text-cyan-700">{{ $totalOnline }}</div>
                <div class="text-xs font-bold text-cyan-600 uppercase tracking-widest mt-1">Mode Online</div>
            </div>
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 text-center">
                <div class="text-3xl font-black text-indigo-700">{{ $totalOffline }}</div>
                <div class="text-xs font-bold text-indigo-600 uppercase tracking-widest mt-1">Mode Offline</div>
            </div>
        </div>

        {{-- Import Form --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-700 mb-3">Import Data Honor Dosen (CSV)</h3>
            <form method="POST" action="{{ route('baa.honor.import') }}" enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf
                <input type="file" name="file" accept=".csv,.txt" required class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all">Import</button>
            </form>
        </div>

        {{-- Requests Table --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Daftar Permohonan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-widest">
                        <tr>
                            <th class="px-5 py-3 text-left">Pengaju</th>
                            <th class="px-5 py-3 text-left">Dosen / Mata Kuliah</th>
                            <th class="px-5 py-3 text-left">Jadwal Asli → Usulan</th>
                            <th class="px-5 py-3 text-left">Ruangan</th>
                            <th class="px-5 py-3 text-center">KBM</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($requests as $req)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $req->pengaju_display_name }}</div>
                                <div class="text-xs text-gray-400">{{ $req->pengaju_nim_nidn }}
                                    <span class="ml-1 px-1.5 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[10px] font-bold uppercase">{{ $req->pengaju_type }}</span>
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $req->created_at->format('d M Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-800">{{ $req->schedule->dosen->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ Str::limit($req->schedule->mata_kuliah, 35) }}</div>
                                <div class="text-xs text-gray-400">Kelas {{ $req->schedule->kelas }} • Pert. {{ $req->schedule->pertemuan }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <div class="text-blue-600">{{ \Carbon\Carbon::parse($req->schedule->waktu_mulai)->format('d M H:i') }} – {{ \Carbon\Carbon::parse($req->schedule->waktu_selesai)->format('H:i') }}</div>
                                <div class="text-purple-600 font-bold mt-1">→ {{ \Carbon\Carbon::parse($req->waktu_mulai_usulan)->format('d M H:i') }} – {{ \Carbon\Carbon::parse($req->waktu_selesai_usulan)->format('H:i') }}</div>
                                @if($req->alasan)
                                <div class="mt-2 text-[10px] text-gray-500 italic max-w-xs truncate">
                                    Alasan: {!! str_replace(['[PENGULANG]', '[BENTROK]'], ['<span class="bg-red-100 text-red-700 font-bold px-1 rounded">[PENGULANG]</span>', '<span class="bg-orange-100 text-orange-700 font-bold px-1 rounded">[BENTROK]</span>'], e($req->alasan)) !!}
                                </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <div class="font-semibold text-gray-700 mb-1">Usulan: {{ $req->ruangan_usulan ?? '-' }}</div>
                                @if($req->status === 'Disetujui' && !$req->is_online)
                                    @if($req->room_id)
                                        <span class="text-green-600 font-bold bg-green-50 px-2 py-1 rounded">Ter-Assign: {{ $req->room->kode_ruangan ?? $req->room->name }}</span>
                                    @else
                                        <button @click="openAssignRoom({{ $req->id }}, '{{ addslashes($req->ruangan_usulan) }}')" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-3 py-1.5 rounded-lg font-bold transition-colors">
                                            Set Ruangan
                                        </button>
                                    @endif
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $req->is_online ? 'bg-cyan-100 text-cyan-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $req->is_online ? 'Online' : 'Offline' }}
                                </span>
                                @if($req->status === 'Disetujui')
                                <form method="POST" action="{{ route('baa.kbm.toggle', $req->id) }}" class="mt-1">
                                    @csrf
                                    <button type="submit" class="text-[10px] text-indigo-500 hover:text-indigo-700 underline">
                                        Ubah ke {{ $req->is_online ? 'Offline' : 'Online' }}
                                    </button>
                                </form>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold
                                    {{ $req->status==='Pending' ? 'bg-yellow-100 text-yellow-800' :
                                       ($req->status==='Disetujui' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $req->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($req->status === 'Disetujui')
                                <button @click="openPresensi({{ $req->id }}, {{ $req->schedule->user_id ?? 'null' }}, '{{ addslashes($req->schedule->dosen->name ?? '') }}')"
                                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all">
                                    Catat Presensi
                                </button>
                                @else
                                <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Presensi Modal --}}
    <div x-show="showPresensi" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-black text-gray-900 mb-1">Catat Presensi Dosen</h3>
            <p class="text-sm text-gray-500 mb-5" x-text="'Dosen: ' + presensiDosen"></p>
            <form method="POST" action="{{ route('baa.presensi.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="schedule_request_id" :value="presensiReqId">
                <input type="hidden" name="dosen_id" :value="presensiDosenId">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Tanggal Hadir</label>
                        <input type="date" name="tanggal_hadir" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Status KBM</label>
                        <select name="status_kbm" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="hadir">Hadir</option>
                            <option value="online">Online</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Jam Mulai</label>
                        <input type="time" name="jam_mulai" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Jam Selesai</label>
                        <input type="time" name="jam_selesai" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Catatan</label>
                    <textarea name="catatan" rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm resize-none"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl">Simpan</button>
                    <button type="button" @click="showPresensi=false" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Assign Room Modal --}}
    <div x-show="showAssignRoom" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-black text-gray-900 mb-1">Tetapkan Ruangan</h3>
            <p class="text-sm text-gray-500 mb-5" x-text="'Usulan Dosen: ' + assignRoomUsulan"></p>
            <form method="POST" :action="`/baa/requests/${assignRoomReqId}/assign-room`" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Pilih Ruangan Master Data</label>
                    <select name="room_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Ruangan --</option>
                        @foreach($rooms as $r)
                        <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->type }}, kap. {{ $r->capacity ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl">Tetapkan</button>
                    <button type="button" @click="showAssignRoom=false" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function baaDashboard() {
    return {
        showPresensi: false,
        presensiReqId: null, presensiDosenId: null, presensiDosen: '',
        
        showAssignRoom: false,
        assignRoomReqId: null, assignRoomUsulan: '',

        openPresensi(reqId, dosenId, dosenName) {
            this.presensiReqId  = reqId;
            this.presensiDosenId = dosenId;
            this.presensiDosen  = dosenName;
            this.showPresensi   = true;
        },
        
        openAssignRoom(reqId, usulan) {
            this.assignRoomReqId = reqId;
            this.assignRoomUsulan = usulan;
            this.showAssignRoom = true;
        }
    };
}
</script>
</x-app-layout>
