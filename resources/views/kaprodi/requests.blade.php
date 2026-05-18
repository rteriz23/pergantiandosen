<x-app-layout>
<x-slot name="header">
    <div class="flex items-center justify-between">
        <h2 class="font-bold text-xl text-gray-800">Persetujuan Pergantian Jadwal</h2>
        <a href="{{ route('kaprodi.calendar') }}"
           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Kalender Dosen
        </a>
    </div>
</x-slot>

<div class="py-8" x-data="kaprodiDashboard()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Stats Bar --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $pending   = $requests->where('status','Pending')->count();
                $approved  = $requests->where('status','Disetujui')->count();
                $rejected  = $requests->where('status','Ditolak')->count();
                $slaBreach = $requests->where('status','Pending')->filter(fn($r)=>$r->sla_status==='breach')->count();
            @endphp
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 text-center">
                <div class="text-3xl font-black text-yellow-700">{{ $pending }}</div>
                <div class="text-xs font-bold text-yellow-600 uppercase tracking-widest mt-1">Pending</div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-2xl p-4 text-center">
                <div class="text-3xl font-black text-green-700">{{ $approved }}</div>
                <div class="text-xs font-bold text-green-600 uppercase tracking-widest mt-1">Disetujui</div>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-center">
                <div class="text-3xl font-black text-red-700">{{ $rejected }}</div>
                <div class="text-xs font-bold text-red-600 uppercase tracking-widest mt-1">Ditolak</div>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-2xl p-4 text-center">
                <div class="text-3xl font-black text-orange-700">{{ $slaBreach }}</div>
                <div class="text-xs font-bold text-orange-600 uppercase tracking-widest mt-1">SLA Breach</div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Filter:</span>
            <a href="{{ route('kaprodi.requests') }}" class="px-4 py-2 rounded-xl text-sm font-semibold {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-indigo-50' }}">Semua</a>
            <a href="{{ route('kaprodi.requests', ['status'=>'Pending']) }}" class="px-4 py-2 rounded-xl text-sm font-semibold {{ request('status')==='Pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-yellow-50' }}">Pending</a>
            <a href="{{ route('kaprodi.requests', ['status'=>'Disetujui']) }}" class="px-4 py-2 rounded-xl text-sm font-semibold {{ request('status')==='Disetujui' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-green-50' }}">Disetujui</a>
            <a href="{{ route('kaprodi.requests', ['status'=>'Ditolak']) }}" class="px-4 py-2 rounded-xl text-sm font-semibold {{ request('status')==='Ditolak' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-red-50' }}">Ditolak</a>
        </div>

        {{-- Request Cards --}}
        <div class="space-y-4">
            @forelse($requests as $req)
            @php
                $slaStatus = $req->sla_status;
                $slaLeft   = $req->sla_hours_left;
                if ($slaStatus === 'breach')  { $slaBg = 'bg-red-50 border-red-300'; }
                elseif ($slaStatus === 'warning') { $slaBg = 'bg-orange-50 border-orange-300'; }
                else { $slaBg = 'bg-white border-gray-200'; }
            @endphp

            <div class="rounded-2xl border shadow-sm overflow-hidden {{ $slaBg }} transition-all hover:shadow-md">
                <div class="p-5">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">

                        {{-- Left: Info --}}
                        <div class="flex-1 space-y-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                {{-- Status Badge --}}
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                                    {{ $req->status === 'Pending' ? 'bg-yellow-100 text-yellow-800' :
                                       ($req->status === 'Disetujui' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $req->status }}
                                </span>
                                {{-- Pengaju Type --}}
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                                    {{ $req->pengaju_type === 'mahasiswa' ? 'bg-purple-100 text-purple-800' : 'bg-indigo-100 text-indigo-800' }}">
                                    {{ $req->pengaju_type }}
                                </span>
                                {{-- Online badge --}}
                                @if($req->is_online)
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-cyan-100 text-cyan-800">ONLINE</span>
                                @endif
                                {{-- SLA Badge (Pending only) --}}
                                @if($req->status === 'Pending' && $req->sla_deadline)
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold
                                    {{ $slaStatus === 'breach' ? 'bg-red-200 text-red-900' :
                                       ($slaStatus === 'warning' ? 'bg-orange-200 text-orange-900' : 'bg-gray-100 text-gray-600') }}">
                                    ⏱ {{ $slaStatus === 'breach' ? 'SLA Breach!' : ($slaLeft . 'j tersisa') }}
                                </span>
                                @endif
                            </div>

                            <div>
                                <p class="font-black text-gray-900 text-base">{{ $req->pengaju_display_name }}</p>
                                <p class="text-xs text-gray-500">{{ $req->pengaju_nim_nidn ? '(' . $req->pengaju_nim_nidn . ')' : '' }} — {{ $req->schedule->prodi->name ?? '-' }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                <div class="bg-white/70 rounded-xl p-3 border border-gray-100">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Mata Kuliah</p>
                                    <p class="font-bold text-gray-800">{{ $req->schedule->mata_kuliah }}</p>
                                    <p class="text-xs text-gray-500">Kelas {{ $req->schedule->kelas }} — Pert. {{ $req->schedule->pertemuan }}</p>
                                </div>
                                <div class="bg-white/70 rounded-xl p-3 border border-gray-100">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Dosen</p>
                                    <p class="font-bold text-gray-800">{{ $req->schedule->dosen->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">NIDN: {{ $req->schedule->dosen->nidn ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                                <div class="bg-blue-50 rounded-xl p-3 border border-blue-100">
                                    <p class="text-xs font-bold text-blue-400 uppercase tracking-widest mb-1">Jadwal Asli</p>
                                    <p class="font-semibold text-blue-800 text-xs">
                                        {{ \Carbon\Carbon::parse($req->schedule->waktu_mulai)->format('l, d M Y') }}<br>
                                        {{ \Carbon\Carbon::parse($req->schedule->waktu_mulai)->format('H:i') }} – {{ \Carbon\Carbon::parse($req->schedule->waktu_selesai)->format('H:i') }}
                                    </p>
                                </div>
                                <div class="bg-purple-50 rounded-xl p-3 border border-purple-100">
                                    <p class="text-xs font-bold text-purple-400 uppercase tracking-widest mb-1">Usulan Baru</p>
                                    <p class="font-semibold text-purple-800 text-xs">
                                        {{ \Carbon\Carbon::parse($req->waktu_mulai_usulan)->format('l, d M Y') }}<br>
                                        {{ \Carbon\Carbon::parse($req->waktu_mulai_usulan)->format('H:i') }} – {{ \Carbon\Carbon::parse($req->waktu_selesai_usulan)->format('H:i') }}
                                    </p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Ruangan</p>
                                    <p class="font-semibold text-gray-800 text-xs">{{ $req->ruangan_usulan ?? '-' }}</p>
                                    <p class="text-xs text-gray-400 mt-1 italic">{{ $req->alasan }}</p>
                                </div>
                            </div>

                            @if($req->catatan_kaprodi)
                            <div class="bg-amber-50 rounded-xl p-3 border border-amber-100 text-xs text-amber-800">
                                <strong>Catatan:</strong> {{ $req->catatan_kaprodi }}
                            </div>
                            @endif
                        </div>

                        {{-- Right: Actions --}}
                        @if($req->status === 'Pending')
                        <div class="flex flex-col gap-2 min-w-[160px]">
                            <button @click="openApprove({{ $req->id }}, '{{ addslashes($req->pengaju_display_name) }} — {{ addslashes($req->schedule->mata_kuliah) }}')"
                                class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-xl transition-all flex items-center justify-center gap-2">
                                ✓ Setujui
                            </button>
                            <button @click="openReject({{ $req->id }}, '{{ addslashes($req->pengaju_display_name) }} — {{ addslashes($req->schedule->mata_kuliah) }}')"
                                class="w-full py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-xl transition-all flex items-center justify-center gap-2">
                                ✕ Tolak
                            </button>
                            <button @click="getSuggestions({{ $req->id }})"
                                class="w-full py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl transition-all border border-indigo-200">
                                💡 Sarankan Jadwal
                            </button>
                        </div>
                        @else
                        <div class="text-xs text-gray-400 min-w-[120px] text-right">
                            {{ optional($req->approved_at)->format('d M Y H:i') ?? optional($req->rejected_at)->format('d M Y H:i') ?? '-' }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
                <p class="text-gray-400 font-medium">Tidak ada permohonan masuk.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Approve Modal --}}
    <div x-show="showApprove" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-black text-gray-900 mb-1">✓ Setujui Permohonan</h3>
            <p class="text-sm text-gray-500 mb-5" x-text="modalDetail"></p>
            <form :action="approveUrl" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Mode KBM</label>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="is_online" value="0" checked class="text-indigo-600"> Offline / Tatap Muka</label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="is_online" value="1" class="text-indigo-600"> Online</label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Catatan untuk Pengaju (Opsional)</label>
                        <textarea name="catatan" rows="3" placeholder="Pesan tambahan..."
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-500"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="submit" class="flex-1 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition-all">Ya, Setujui</button>
                    <button type="button" @click="showApprove=false" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-all">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-show="showReject" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-black text-gray-900 mb-1">✕ Tolak Permohonan</h3>
            <p class="text-sm text-gray-500 mb-5" x-text="modalDetail"></p>
            <form :action="rejectUrl" method="POST">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Alasan Penolakan *</label>
                    <textarea name="catatan" rows="3" required placeholder="Jelaskan alasan penolakan..."
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="submit" class="flex-1 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all">Ya, Tolak</button>
                    <button type="button" @click="showReject=false" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-all">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Suggest Slots Modal --}}
    <div x-show="showSuggest" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-black text-gray-900 mb-1">💡 Saran Jadwal Alternatif</h3>
            <p class="text-sm text-gray-500 mb-4" x-text="suggestDosen ? 'Jadwal kosong untuk: ' + suggestDosen : 'Memuat...'"></p>
            <div x-show="loadingSuggest" class="text-center py-6 text-gray-400">Memuat saran...</div>
            <div x-show="!loadingSuggest" class="space-y-2">
                <template x-for="slot in suggestSlots" :key="slot.date">
                    <div class="flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-xl">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <span class="text-sm font-semibold text-green-800" x-text="slot.label"></span>
                    </div>
                </template>
                <div x-show="suggestSlots.length === 0 && !loadingSuggest" class="text-center py-4 text-gray-400 text-sm">Tidak ada jadwal kosong ditemukan dalam 4 minggu ke depan.</div>
            </div>
            <button @click="showSuggest=false" class="mt-5 w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-all">Tutup</button>
        </div>
    </div>
</div>

<script>
function kaprodiDashboard() {
    return {
        showApprove: false, showReject: false, showSuggest: false,
        approveUrl: '', rejectUrl: '',
        modalDetail: '',
        suggestSlots: [], suggestDosen: '', loadingSuggest: false,

        openApprove(id, detail) {
            this.approveUrl = `/kaprodi/requests/${id}/approve`;
            this.modalDetail = detail;
            this.showApprove = true;
        },
        openReject(id, detail) {
            this.rejectUrl = `/kaprodi/requests/${id}/reject`;
            this.modalDetail = detail;
            this.showReject = true;
        },
        async getSuggestions(id) {
            this.showSuggest = true;
            this.loadingSuggest = true;
            this.suggestSlots = [];
            try {
                const res = await fetch(`/kaprodi/requests/${id}/suggest-slots`);
                const data = await res.json();
                this.suggestSlots = data.slots || [];
                this.suggestDosen = data.dosen || '';
            } catch(e) { console.error(e); }
            this.loadingSuggest = false;
        }
    };
}
</script>
</x-app-layout>
