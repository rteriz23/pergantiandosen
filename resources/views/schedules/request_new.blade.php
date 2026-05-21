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
                Manajemen Pergantian Jadwal — LPKIA
            </span>
            <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-700 to-purple-600 tracking-tight mb-2">
                Pengajuan Jadwal Pengganti
            </h1>
            <p class="text-gray-500">Dosen: <span class="font-bold text-indigo-700" x-text="selectedDosenName || 'Silakan Pilih Dosen'"></span></p>
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

                {{-- Fields for Dosen --}}
                <div x-show="pengajuType === 'dosen'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Nama Dosen</label>
                        @if($dosen)
                            <input type="text" name="pengaju_nama_dosen" placeholder="Nama dosen"
                                :value="selectedDosenName" readonly
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-600 cursor-not-allowed">
                        @else
                            <select id="dosen_select" @change="changeDosen()" required
                                class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Pilih Dosen --</option>
                                @foreach($allDosens as $d)
                                    <option value="{{ $d->id }}" data-nidn="{{ $d->nidn ?? '' }}" data-name="{{ $d->name }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="pengaju_nama_dosen" :value="selectedDosenName">
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">NIDN</label>
                        <input type="text" name="pengaju_nidn_dosen" placeholder="NIDN"
                            :value="dosenNidn" readonly
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-600 cursor-not-allowed">
                    </div>
                </div>

                {{-- Fields for Mahasiswa -- smart NIM lookup --}}
                <div x-show="pengajuType === 'mahasiswa'" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">NIM / NRP *</label>
                            <div class="relative">
                                <input type="text" name="pengaju_nim_nidn" id="nim_input"
                                    x-model="studentNim"
                                    @input.debounce.600ms="lookupMahasiswa()"
                                    :required="pengajuType === 'mahasiswa'"
                                    placeholder="Masukkan NIM, sistem akan cek otomatis..."
                                    class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent pr-10">
                                <div x-show="nimLoading" class="absolute right-3 top-3.5">
                                    <svg class="animate-spin w-4 h-4 text-purple-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Nama Mahasiswa *</label>
                            <input type="text" name="pengaju_nama" id="nama_input"
                                x-model="studentNama"
                                :required="pengajuType === 'mahasiswa'"
                                placeholder="Otomatis terisi saat NIM valid..."
                                class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Email (Opsional)</label>
                            <input type="email" name="pengaju_email" id="email_input"
                                x-model="studentEmail"
                                placeholder="email@contoh.com"
                                class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                    </div>

                    {{-- Student Info Panel (shown after NIM lookup) --}}
                    <div x-show="studentInfo !== null" x-transition class="rounded-xl border-2 overflow-hidden"
                         :class="studentInfo && studentInfo.status_mengulang ? 'border-red-200 bg-red-50' : 'border-green-200 bg-green-50'">
                        <div class="flex items-center justify-between px-5 py-3"
                             :class="studentInfo && studentInfo.status_mengulang ? 'bg-red-100' : 'bg-green-100'">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-black text-white"
                                     :class="studentInfo && studentInfo.status_mengulang ? 'bg-red-500' : 'bg-green-600'"
                                     x-text="studentInfo ? studentInfo.nama.charAt(0).toUpperCase() : ''"></div>
                                <div>
                                    <div class="font-bold text-gray-900 text-sm" x-text="studentInfo ? studentInfo.nama : ''"></div>
                                    <div class="text-xs text-gray-600" x-text="studentInfo ? (studentInfo.prodi + ' — Kelas ' + studentInfo.kelas) : ''"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-full text-xs font-black uppercase"
                                      :class="studentInfo && studentInfo.status_mengulang ? 'bg-red-200 text-red-800' : 'bg-green-200 text-green-800'"
                                      x-text="studentInfo && studentInfo.status_mengulang ? '⚠ Mengulang (maks. <6 SKS)' : '✓ Status Reguler'"></span>
                            </div>
                        </div>

                        {{-- Student's Course List --}}
                        <div x-show="studentJadwals && studentJadwals.length > 0" class="px-5 py-4">
                            <div class="text-xs font-bold uppercase text-gray-500 mb-3">
                                📚 Jadwal Perkuliahan Mahasiswa Ini (<span x-text="studentJadwals ? studentJadwals.length : 0"></span> mata kuliah):
                            </div>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="j in studentJadwals" :key="j.id">
                                    <div class="flex items-center justify-between bg-white/70 rounded-lg px-3 py-2 text-xs border border-white/50">
                                        <div class="font-semibold text-gray-800" x-text="j.mata_kuliah"></div>
                                        <div class="flex items-center gap-3 text-gray-500 shrink-0 ml-3">
                                            <span class="font-medium" x-text="j.dosen_nama"></span>
                                            <span class="bg-gray-100 px-2 py-0.5 rounded font-bold text-gray-700" x-text="j.hari + ', ' + j.jam"></span>
                                            <span class="bg-indigo-100 px-2 py-0.5 rounded text-indigo-700 font-bold" x-text="j.ruangan"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div x-show="studentJadwals && studentJadwals.length === 0" class="px-5 py-3 text-xs text-gray-500">
                            ℹ️ Belum ada data jadwal untuk kelas mahasiswa ini. Sistem tidak bisa melakukan pengecekan bentrok otomatis.
                        </div>
                    </div>

                    {{-- NIM not found warning --}}
                    <div x-show="nimNotFound" x-transition
                         class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 flex items-start gap-2">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <div class="font-bold">NIM tidak ditemukan di sistem.</div>
                            <div class="text-xs mt-0.5">Mahasiswa baru akan otomatis didaftarkan saat pengajuan disimpan. Pastikan nama dan email diisi dengan benar.</div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Original Schedule --}}
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/60 p-6">
                <h3 class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4">1 — Pilih Jadwal yang Akan Diganti</h3>
                <select id="original_schedule_select" @change="updateOriginal()" required
                    class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Mata Kuliah / Jadwal --</option>
                    <template x-for="s in schedulesList" :key="s.id">
                        <option :value="s.id" x-text="s.mata_kuliah + ' (' + s.kelas + ') — Pert. ' + s.pertemuan"></option>
                    </template>
                </select>
                <div x-show="originalDetails !== ''" x-text="originalDetails"
                    class="mt-3 p-3 bg-indigo-50 rounded-xl text-xs text-indigo-700 font-medium border border-indigo-100"></div>
            </div>

            {{-- Dosen Pengganti Selection --}}
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/60 p-6">
                <h3 class="text-xs font-bold text-teal-600 uppercase tracking-widest mb-4">Dosen Pengganti (Opsional)</h3>
                <select name="dosen_pengganti_id" x-model="substituteId" @change="checkAvailability()"
                    class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-teal-500">
                    <option value="">-- Pilih Dosen Pengganti (Tetap Mengajar Sendiri) --</option>
                    @foreach($dosens as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-2">Pilih dosen pengganti jika Anda ingin menugaskan dosen lain untuk menggantikan kelas ini.</p>
            </div>

            {{-- Proposed Schedule --}}
            {{-- Proposed Schedule & Room --}}
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/60 p-6">
                <h3 class="text-xs font-bold text-purple-600 uppercase tracking-widest mb-4">2 — Usulan Jadwal & Ruangan Pengganti</h3>
                
                <!-- First Row: Tanggal and Ruangan Usulan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Tanggal Usulan *</label>
                        <input type="date" name="waktu_mulai_usulan_date" x-model="proposedDate" @change="checkAvailability()" required
                            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Ruangan Usulan *</label>
                        <input type="text" name="ruangan_usulan" x-model="proposedRoom" @input.debounce.500ms="checkAvailability()" required
                            list="roomSuggestions" placeholder="Ketik nama ruangan atau pilih dari daftar..."
                            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500">
                        <datalist id="roomSuggestions">
                            @foreach($rooms as $r)
                            <option value="{{ $r->name }}">{{ $r->name }} ({{ $r->type }}, kap. {{ $r->capacity ?? '-' }})</option>
                            @endforeach
                        </datalist>
                        <div x-show="proposedRoom" class="mt-2 text-xs font-semibold text-indigo-600 flex items-center gap-1">
                            <span>🔍 Memeriksa ketersediaan <span x-text="proposedRoom" class="font-bold underline"></span> secara real-time...</span>
                        </div>
                    </div>
                </div>

                <!-- Second Row: Jam Mulai, Jam Selesai, Mode Pengajaran -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Jam Mulai *</label>
                        <input type="time" name="waktu_mulai_usulan_time" x-model="proposedStart" @change="checkAvailability()" required
                            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Jam Selesai *</label>
                        <input type="time" name="waktu_selesai_usulan_time" x-model="proposedEnd" @change="checkAvailability()" required
                            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Mode Pengajaran *</label>
                        <select name="is_online" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500">
                            <option value="0">Tatap Muka (Offline)</option>
                            <option value="1">Online</option>
                        </select>
                    </div>
                </div>

                {{-- Availability status box --}}
                <div x-show="proposedDate" x-transition class="mt-4 p-5 rounded-2xl border transition-all" :class="statusColor">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-0.5 text-xl" x-text="statusIcon"></div>
                        <div class="ml-3 w-full">
                            <h3 class="text-sm font-extrabold uppercase tracking-wide" :class="statusTextColor" x-text="statusTitle"></h3>
                            <div class="mt-1 text-sm font-medium" :class="statusTextColor" x-html="statusMessage"></div>
                            
                            <div class="mt-3 pt-3 border-t border-current/10 flex flex-wrap gap-2 items-center text-xs" :class="statusTextColor">
                                <span class="font-bold uppercase tracking-wider opacity-75">Detail Verifikasi:</span>
                                <span class="bg-black/5 px-2.5 py-1 rounded-md font-bold" x-text="proposedDate"></span>
                                <span class="bg-black/5 px-2.5 py-1 rounded-md font-bold" x-text="proposedStart + ' - ' + proposedEnd"></span>
                                <span class="bg-black/5 px-2.5 py-1 rounded-md font-black" x-text="'Ruang: ' + (proposedRoom || 'Belum diisi') + (proposedRoomType ? ' (' + proposedRoomType + ')' : '')"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alasan --}}
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/60 p-6">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">3 — Alasan Pergantian</h3>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Alasan Pergantian *</label>
                    <textarea name="alasan" required rows="3"
                        placeholder="Jelaskan alasan pemindahan jadwal secara singkat..."
                        class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between">
                <a href="{{ $dosen ? route('schedules.public', ['dosen_id' => $dosen->id]) : route('schedules.public') }}"
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
        dosenId: '{{ $dosen->id ?? "" }}',
        dosenNidn: '{{ $dosen->nidn ?? "" }}',
        selectedDosenName: '{{ $dosen->name ?? "" }}',
        schedulesList: [],
        selectedScheduleId: '',
        originalDetails: '',
        proposedDate: '{{ $prefilledTime ? $prefilledTime->format("Y-m-d") : "" }}',
        proposedStart: '{{ $prefilledTime ? $prefilledTime->format("H:i") : "07:30" }}',
        proposedEnd: '{{ isset($prefilledEndTime) && $prefilledEndTime ? $prefilledEndTime->format("H:i") : ($prefilledTime ? $prefilledTime->copy()->addHours(2)->format("H:i") : "09:40") }}',
        proposedRoom: '{{ $prefilledRoom ?? "" }}',
        proposedRoomType: '',
        substituteId: '',
        isBlocked: false,
        statusTitle: 'Pilih tanggal untuk cek ketersediaan',
        statusMessage: 'Masukkan tanggal, jam, dan ruangan usulan untuk memeriksa ketersediaan sistem.',
        statusColor: 'bg-gray-50 border-gray-200',
        statusTextColor: 'text-gray-600',
        statusIcon: '📅',

        // ── Mahasiswa NIM lookup ─────────────────────────────────
        studentNim: '',
        studentNama: '',
        studentEmail: '',
        studentInfo: null,
        studentJadwals: [],
        nimLoading: false,
        nimNotFound: false,

        init() {
            @if($dosen)
            this.schedulesList = [
                @foreach($schedules as $s)
                {
                    id: '{{ $s->id }}',
                    mata_kuliah: '{{ $s->mata_kuliah }}',
                    kelas: '{{ $s->kelas }}',
                    pertemuan: '{{ $s->pertemuan }}',
                    details: '{{ $s->mata_kuliah }} — Kelas {{ $s->kelas }}, Pertemuan {{ $s->pertemuan }} | {{ \Carbon\Carbon::parse($s->waktu_mulai)->format("l, d M Y H:i") }} | Ruangan Asli: {{ $s->room->name ?? "-" }} ({{ $s->room->type ?? "N/A" }})'
                },
                @endforeach
            ];
            @endif
            if (this.proposedDate) this.checkAvailability();
        },

        async changeDosen() {
            const sel = document.getElementById('dosen_select');
            if (!sel || !sel.value) {
                this.dosenId = '';
                this.dosenNidn = '';
                this.selectedDosenName = '';
                this.schedulesList = [];
                this.selectedScheduleId = '';
                this.originalDetails = '';
                return;
            }
            const opt = sel.options[sel.selectedIndex];
            this.dosenId = opt.value;
            this.dosenNidn = opt.getAttribute('data-nidn') || '';
            this.selectedDosenName = opt.getAttribute('data-name') || '';
            this.selectedScheduleId = '';
            this.originalDetails = '';

            try {
                const res = await fetch(`/api/dosen/${this.dosenId}/schedules`);
                const data = await res.json();
                this.schedulesList = data;
            } catch(e) {
                console.error('Fetch lecturer schedules error:', e);
            }
            this.checkAvailability();
        },

        updateOriginal() {
            const sel = document.getElementById('original_schedule_select');
            this.selectedScheduleId = sel.value;
            const matched = this.schedulesList.find(s => String(s.id) === String(this.selectedScheduleId));
            this.originalDetails = matched ? matched.details : '';
            this.checkAvailability();
        },

        async lookupMahasiswa() {
            const nim = this.studentNim.trim();
            if (nim.length < 5) {
                this.studentInfo = null;
                this.studentJadwals = [];
                this.nimNotFound = false;
                return;
            }
            this.nimLoading = true;
            this.nimNotFound = false;
            try {
                const res = await fetch(`/api/mahasiswa/${encodeURIComponent(nim)}/jadwal`);
                if (res.status === 404) {
                    this.studentInfo = null;
                    this.studentJadwals = [];
                    this.nimNotFound = true;
                } else {
                    const data = await res.json();
                    this.studentInfo = data.mahasiswa;
                    this.studentJadwals = data.jadwals;
                    this.nimNotFound = false;
                    // Auto-fill nama dan email jika kosong
                    if (!this.studentNama && data.mahasiswa.nama) this.studentNama = data.mahasiswa.nama;
                    if (!this.studentEmail && data.mahasiswa.email) this.studentEmail = data.mahasiswa.email;
                }
            } catch(e) {
                console.error('Lookup NIM error:', e);
            } finally {
                this.nimLoading = false;
            }
        },

        async checkAvailability() {
            if (!this.dosenId || !this.proposedDate || !this.proposedStart || !this.proposedEnd) return;
            const dateObj = new Date(this.proposedDate);
            if (dateObj.getDay() === 0) {
                this.isBlocked = true;
                this.statusTitle = 'Hari Minggu tidak diizinkan';
                this.statusMessage = 'Pengajuan pergantian jadwal hanya diperbolehkan untuk hari kerja (Senin - Sabtu).';
                this.statusColor = 'bg-red-50 border-red-200';
                this.statusTextColor = 'text-red-700';
                this.statusIcon = '❌';
                this.proposedRoomType = '';
                return;
            }

            try {
                const res = await fetch(`/api/availability?date=${this.proposedDate}&start_time=${this.proposedStart}&end_time=${this.proposedEnd}&dosen_id=${this.dosenId}&dosen_pengganti_id=${this.substituteId}&schedule_id=${this.selectedScheduleId}&room=${encodeURIComponent(this.proposedRoom)}`);
                const data = await res.json();

                this.proposedRoomType = data.room_details ? data.room_details.type : '';

                if (data.dosen_schedule_conflict) {
                    this.isBlocked = true;
                    this.statusTitle = '⚠️ Jadwal / Kuota Dosen Bentrok!';
                    this.statusMessage = data.message.replace(/\n/g, '<br>') || 'Dosen yang bersangkutan sudah memiliki jadwal mengajar reguler lain atau melebihi kuota harian pada jam terpilih.';
                    this.statusColor = 'bg-red-50 border-red-200';
                    this.statusTextColor = 'text-red-700';
                    this.statusIcon = '❌';
                } else if (data.dosen_request_conflict) {
                    this.isBlocked = true;
                    this.statusTitle = '⚠️ Permohonan Bentrok!';
                    this.statusMessage = data.message.replace(/\n/g, '<br>') || 'Dosen yang bersangkutan memiliki permohonan pengganti lain pada jam terpilih.';
                    this.statusColor = 'bg-red-50 border-red-200';
                    this.statusTextColor = 'text-red-700';
                    this.statusIcon = '❌';
                } else if (data.room_conflict) {
                    this.isBlocked = true;
                    this.statusTitle = `❌ Ruangan bentrok!`;
                    this.statusMessage = `Ruangan <strong>${this.proposedRoom}</strong> terpakai oleh: <strong>${data.room_conflict.dosen}</strong> (${data.room_conflict.mata_kuliah} - ${data.room_conflict.kelas}) pada jam ${data.room_conflict.waktu}.`;
                    this.statusColor = 'bg-orange-50 border-orange-200';
                    this.statusTextColor = 'text-orange-800';
                    this.statusIcon = '🔒';
                } else {
                    // Cek bentrok jadwal mahasiswa jika mode mahasiswa
                    if (this.pengajuType === 'mahasiswa' && this.studentJadwals && this.studentJadwals.length > 0) {
                        const start = new Date(`${this.proposedDate}T${this.proposedStart}`);
                        const end   = new Date(`${this.proposedDate}T${this.proposedEnd}`);
                        const conflict = this.studentJadwals.find(j => {
                            const jStart = new Date(j.waktu_mulai);
                            const jEnd   = new Date(j.waktu_selesai);
                            return start < jEnd && end > jStart;
                        });
                        if (conflict) {
                            if (this.studentInfo && this.studentInfo.status_mengulang) {
                                // Pengulang: warning tapi boleh lanjut
                                this.isBlocked = false;
                                this.statusTitle = '⚠️ Potensi bentrok jadwal pengulang';
                                this.statusMessage = `Waktu ini bertabrakan dengan jadwal reguler Anda: <strong>${conflict.mata_kuliah}</strong> (${conflict.hari}, ${conflict.jam}). Karena status Anda MENGULANG, sistem tetap mengizinkan pengajuan — AI akan menghitung solusi terbaik.`;
                                this.statusColor = 'bg-amber-50 border-amber-200';
                                this.statusTextColor = 'text-amber-800';
                                this.statusIcon = '🤖';
                            } else {
                                // Reguler: blokir
                                this.isBlocked = true;
                                this.statusTitle = '❌ Bentrok dengan jadwal kuliah Anda!';
                                this.statusMessage = `Waktu ini bertabrakan dengan mata kuliah Anda: <strong>${conflict.mata_kuliah}</strong> bersama <strong>${conflict.dosen_nama}</strong> di ${conflict.ruangan} (${conflict.hari}, ${conflict.jam}).`;
                                this.statusColor = 'bg-red-50 border-red-200';
                                this.statusTextColor = 'text-red-700';
                                this.statusIcon = '❌';
                            }
                            return;
                        }
                    }
                    this.isBlocked = false;
                    this.statusTitle = '✓ Jadwal dan ruangan tersedia';
                    this.statusMessage = `Jadwal usulan dan ruangan <strong>${this.proposedRoom || 'yang dipilih'}</strong> aman dan dapat digunakan untuk pergantian jadwal.`;
                    this.statusColor = 'bg-green-50 border-green-200';
                    this.statusTextColor = 'text-green-700';
                    this.statusIcon = '✅';
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
