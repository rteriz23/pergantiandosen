<x-guest-layout>
<div class="fixed inset-0 z-0 overflow-hidden bg-gradient-to-br from-slate-50 via-indigo-50 to-purple-50">
    <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-300 mix-blend-multiply filter blur-[120px] opacity-50 animate-blob"></div>
    <div class="absolute top-[20%] right-[-10%] w-[50%] h-[50%] rounded-full bg-purple-300 mix-blend-multiply filter blur-[120px] opacity-50 animate-blob animation-delay-2000"></div>
</div>

<div class="relative z-10 min-h-screen py-12 px-4 sm:px-6 lg:px-8 font-sans"
     x-data="requestForm()"
     x-init="init()">
    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="text-center mb-10">
            <span class="inline-block py-1 px-3 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-sm font-semibold tracking-wide mb-3">
                Sistem Pergantian Jadwal — LPKIA
            </span>
            <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-700 to-purple-600 tracking-tight mb-2">
                Pengajuan Jadwal Pengganti
            </h1>
            <p class="text-gray-500">Dosen: <span class="font-bold text-indigo-700">{{ $dosen->name }}</span></p>
        </div>

        @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $err)<li class="text-sm">{{ $err }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form id="requestForm" method="POST" class="space-y-6" :action="'/schedules/request/' + selectedScheduleId">
            @csrf

            {{-- Pengaju Type Tabs --}}
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/60 p-6">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Pengaju</h3>
                <div class="flex gap-3 mb-5">
                    <button type="button" @click="pengajuType='dosen'"
                        :class="pengajuType==='dosen' ? 'bg-indigo-600 text-white shadow-indigo-200 shadow-lg' : 'bg-gray-100 text-gray-600 hover:bg-indigo-50'"
                        class="flex-1 py-3 rounded-xl font-bold text-sm transition-all">
                        🎓 Dosen
                    </button>
                    <button type="button" @click="pengajuType='mahasiswa'"
                        :class="pengajuType==='mahasiswa' ? 'bg-purple-600 text-white shadow-purple-200 shadow-lg' : 'bg-gray-100 text-gray-600 hover:bg-purple-50'"
                        class="flex-1 py-3 rounded-xl font-bold text-sm transition-all">
                        📚 Mahasiswa
                    </button>
                </div>
                <input type="hidden" name="pengaju_type" :value="pengajuType">

                @guest
                {{-- Anonymous fields - shown when not logged in --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">
                            <span x-text="pengajuType==='mahasiswa' ? 'Nama Mahasiswa' : 'Nama Dosen'"></span> *
                        </label>
                        <input type="text" name="pengaju_nama" required placeholder="Nama lengkap"
                            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">
                            <span x-text="pengajuType==='mahasiswa' ? 'NIM' : 'NIDN'"></span> *
                        </label>
                        <input type="text" name="pengaju_nim_nidn" required :placeholder="pengajuType==='mahasiswa' ? '12345678' : '0401019001'"
                            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Email (Opsional)</label>
                        <input type="email" name="pengaju_email" placeholder="email@contoh.com"
                            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                {{-- Mahasiswa quota display --}}
                <div x-show="pengajuType==='mahasiswa'" class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Batas pengajuan mahasiswa berlaku. Pastikan NIM yang dimasukkan benar.
                </div>
                @endguest
            </div>

            {{-- Original Schedule --}}
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/60 p-6">
                <h3 class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4">1 — Pilih Jadwal yang Akan Diganti</h3>
                <select id="original_schedule_select" @change="updateOriginal()" required
                    class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Mata Kuliah / Jadwal --</option>
                    @foreach($schedules as $s)
                    <option value="{{ $s->id }}"
                        data-details="{{ $s->mata_kuliah }} — Kelas {{ $s->kelas }}, Pertemuan {{ $s->pertemuan }} | {{ \Carbon\Carbon::parse($s->waktu_mulai)->format('l, d M Y H:i') }}">
                        {{ $s->mata_kuliah }} ({{ $s->kelas }}) — Pert. {{ $s->pertemuan }}
                    </option>
                    @endforeach
                </select>
                <div x-show="originalDetails !== ''" x-text="originalDetails"
                    class="mt-3 p-3 bg-indigo-50 rounded-xl text-xs text-indigo-700 font-medium border border-indigo-100"></div>
            </div>

            {{-- Proposed Schedule --}}
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/60 p-6">
                <h3 class="text-xs font-bold text-purple-600 uppercase tracking-widest mb-4">2 — Usulan Jadwal Pengganti</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Tanggal</label>
                        <input type="date" name="waktu_mulai_usulan_date" x-model="proposedDate" @change="checkAvailability()" required
                            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Jam Mulai</label>
                        <input type="time" name="waktu_mulai_usulan_time" x-model="proposedStart" required
                            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Jam Selesai</label>
                        <input type="time" name="waktu_selesai_usulan_time" x-model="proposedEnd" required
                            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                {{-- Availability badge --}}
                <div x-show="proposedDate" x-transition class="mt-4 p-4 rounded-xl border flex items-center gap-3" :class="statusColor">
                    <span :class="statusTextColor" x-html="statusIcon"></span>
                    <span class="text-sm font-semibold" :class="statusTextColor" x-text="statusTitle"></span>
                </div>
            </div>

            {{-- Room & Reason --}}
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/60 p-6">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">3 — Ruangan & Alasan</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Ruangan Usulan *</label>
                        <input type="text" name="ruangan_usulan" x-model="proposedRoom"
                            @input.debounce.600ms="checkAvailability()" required
                            list="roomSuggestions"
                            placeholder="Ketik nama ruangan atau pilih dari daftar..."
                            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                        <datalist id="roomSuggestions">
                            @foreach($rooms as $r)
                            <option value="{{ $r->name }}">{{ $r->name }} ({{ $r->type }}, kap. {{ $r->capacity ?? '-' }})</option>
                            @endforeach
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Mode Pengajaran *</label>
                        <select name="is_online" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="0">Tatap Muka (Offline)</option>
                            <option value="1">Online</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Alasan Pergantian *</label>
                        <textarea name="alasan" required rows="3"
                            placeholder="Jelaskan alasan pemindahan jadwal secara singkat..."
                            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('schedules.public', ['dosen_id' => $dosen->id]) }}"
                    class="text-gray-500 hover:text-indigo-600 font-medium flex items-center gap-1 transition-colors">
                    ← Kembali ke Kalender
                </a>
                <button type="submit" :disabled="selectedScheduleId === '' || isBlocked"
                    class="px-10 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-2xl shadow-lg hover:from-indigo-700 hover:to-purple-700 transform transition-all hover:-translate-y-0.5 focus:ring-4 focus:ring-indigo-500/30 disabled:opacity-40 disabled:cursor-not-allowed disabled:transform-none">
                    🚀 Kirim Permohonan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function requestForm() {
    return {
        pengajuType: 'dosen',
        selectedScheduleId: '',
        originalDetails: '',
        proposedDate: '{{ $prefilledTime ? $prefilledTime->format("Y-m-d") : "" }}',
        proposedStart: '{{ $prefilledTime ? $prefilledTime->format("H:i") : "07:30" }}',
        proposedEnd: '{{ $prefilledTime ? $prefilledTime->copy()->addHours(2)->format("H:i") : "09:40" }}',
        proposedRoom: '',
        isBlocked: false,
        statusTitle: 'Pilih tanggal untuk cek ketersediaan',
        statusColor: 'bg-gray-50 border-gray-200',
        statusTextColor: 'text-gray-600',
        statusIcon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',

        init() {
            if (this.proposedDate) this.checkAvailability();
        },

        updateOriginal() {
            const sel = document.getElementById('original_schedule_select');
            this.selectedScheduleId = sel.value;
            const opt = sel.options[sel.selectedIndex];
            this.originalDetails = opt.getAttribute('data-details') || '';
        },

        async checkAvailability() {
            if (!this.proposedDate) return;
            const dateObj = new Date(this.proposedDate);
            if (dateObj.getDay() === 0) {
                this.isBlocked = true;
                this.statusTitle = 'Hari Minggu tidak diizinkan';
                this.statusColor = 'bg-red-50 border-red-200';
                this.statusTextColor = 'text-red-700';
                return;
            }

            try {
                const res = await fetch(`/api/availability?date=${this.proposedDate}&dosen_id={{ $dosen->id }}&room=${encodeURIComponent(this.proposedRoom)}`);
                const data = await res.json();

                if (data.schedules && data.schedules.length > 0) {
                    this.isBlocked = false;
                    this.statusTitle = 'Ada jadwal lain di hari ini — pastikan jam tidak bertabrakan';
                    this.statusColor = 'bg-yellow-50 border-yellow-200';
                    this.statusTextColor = 'text-yellow-800';
                } else if (data.room_conflicts && data.room_conflicts.length > 0) {
                    this.isBlocked = false;
                    this.statusTitle = 'Ruangan sudah digunakan pengajuan lain di hari ini';
                    this.statusColor = 'bg-orange-50 border-orange-200';
                    this.statusTextColor = 'text-orange-800';
                } else {
                    this.isBlocked = false;
                    this.statusTitle = '✓ Jadwal dan ruangan tersedia';
                    this.statusColor = 'bg-green-50 border-green-200';
                    this.statusTextColor = 'text-green-700';
                }
            } catch(e) {
                console.error(e);
            }
        }
    };
}
</script>

<style>
@keyframes blob { 0%{transform:translate(0,0) scale(1)} 33%{transform:translate(30px,-50px) scale(1.1)} 66%{transform:translate(-20px,20px) scale(0.9)} 100%{transform:translate(0,0) scale(1)} }
.animate-blob { animation: blob 7s infinite; }
.animation-delay-2000 { animation-delay: 2s; }
</style>
</x-guest-layout>
