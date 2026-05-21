<x-guest-layout>
    <div class="py-12 bg-gray-50 min-h-screen" x-data="scheduleRequest()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
            
            <!-- Left Column: Current Schedule Info -->
            <div class="w-full md:w-1/3">
                <div class="glass bg-white overflow-hidden shadow-xl sm:rounded-2xl p-6 relative border border-gray-100">
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-400 to-indigo-500"></div>
                    
                    <div class="mb-4 mt-2">
                        <a href="{{ route('schedules.public', ['dosen_id' => $schedule->user_id, 'periode' => $schedule->periode]) }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Kembali ke Kalender
                        </a>
                    </div>

                    <h3 class="text-xl font-bold text-gray-800 mb-6">Jadwal Saat Ini</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Dosen</p>
                            <p class="text-lg text-gray-900 font-semibold">{{ $schedule->dosen->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Mata Kuliah</p>
                            <p class="text-lg text-gray-900 font-semibold">{{ $schedule->mata_kuliah }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Kelas / Pertemuan</p>
                            <p class="text-md text-gray-800">{{ $schedule->kelas }} / Ke-{{ $schedule->pertemuan }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Ruangan Saat Ini</p>
                            <p class="text-md text-gray-900 font-semibold">{{ $schedule->room->name ?? '-' }} ({{ $schedule->room->type ?? 'N/A' }})</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Waktu Mulai</p>
                            <p class="text-md text-gray-800 font-medium bg-blue-50 inline-block px-3 py-1 rounded-lg text-blue-700 mt-1">
                                {{ \Carbon\Carbon::parse($schedule->waktu_mulai)->format('l, d M Y - H:i') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Waktu Selesai</p>
                            <p class="text-md text-gray-800 font-medium bg-red-50 inline-block px-3 py-1 rounded-lg text-red-700 mt-1">
                                {{ \Carbon\Carbon::parse($schedule->waktu_selesai)->format('l, d M Y - H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Form & Calendar -->
            <div class="w-full md:w-2/3">
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl p-8 border border-gray-100">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Manajemen Pergantian Jadwal - Pilih Jadwal Pengganti</h3>
                    
                    @if ($errors->any())
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl relative" role="alert">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li class="text-sm">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('schedules.storeRequest', $schedule->id) }}">
                        @csrf
                        
                        @guest
                        <div class="mb-6 bg-indigo-50/50 rounded-2xl p-6 border border-indigo-100/80">
                            <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4">Identitas Pengaju (Tamu)</h4>
                            
                            <!-- Role Switch Selector -->
                            <div class="flex bg-gray-100 p-1 rounded-xl mb-4 max-w-xs">
                                <button type="button" @click="pengajuType = 'dosen'"
                                    :class="pengajuType === 'dosen' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                                    class="flex-1 py-2 px-3 text-xs font-bold rounded-lg transition-all focus:outline-none">
                                    👨‍🏫 Dosen
                                </button>
                                <button type="button" @click="pengajuType = 'mahasiswa'"
                                    :class="pengajuType === 'mahasiswa' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                                    class="flex-1 py-2 px-3 text-xs font-bold rounded-lg transition-all focus:outline-none">
                                    🎓 Mahasiswa
                                </button>
                            </div>
                            
                            <!-- Hidden input to submit pengaju_type -->
                            <input type="hidden" name="pengaju_type" :value="pengajuType">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">
                                        <span x-text="pengajuType==='mahasiswa' ? 'Nama Mahasiswa' : 'Nama Dosen'"></span> *
                                    </label>
                                    <input type="text" name="pengaju_nama" required placeholder="Nama lengkap"
                                        :value="pengajuType === 'dosen' ? '{{ addslashes($schedule->dosen->name ?? '') }}' : ''"
                                        class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">
                                        <span x-text="pengajuType==='mahasiswa' ? 'NIM' : 'NIDN'"></span> *
                                    </label>
                                    <input type="text" name="pengaju_nim_nidn" required :placeholder="pengajuType==='mahasiswa' ? '12345678' : '0401019001'"
                                        :value="pengajuType === 'dosen' ? '0401019001' : ''"
                                        class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Email (Opsional)</label>
                                    <input type="email" name="pengaju_email" placeholder="email@contoh.com"
                                        class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                            </div>

                            {{-- Mahasiswa quota display --}}
                            <div x-show="pengajuType==='mahasiswa'" class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 flex items-start gap-2">
                                <span class="text-base mt-0.5">⚠️</span>
                                <span>Batas pengajuan mahasiswa berlaku (maksimal total 6 SKS). Pastikan NIM yang dimasukkan benar.</span>
                            </div>
                        </div>
                        @else
                        <!-- Hidden role inputs for logged-in user -->
                        <input type="hidden" name="pengaju_type" value="dosen">
                        @endguest
                        
                        <!-- Dosen Pengganti (Opsional) Dropdown -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Dosen Pengganti (Opsional)</label>
                            <select name="dosen_pengganti_id" x-model="substituteId" @change="checkAvailability" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 text-lg py-3 px-4 transition-all">
                                <option value="">-- Pilih Dosen Pengganti (Tetap Mengajar Sendiri) --</option>
                                @foreach($dosens as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Pilih dosen pengganti jika Anda ingin menugaskan dosen lain untuk menggantikan kelas Anda.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Usulan *</label>
                                <input type="date" x-model="selectedDate" @change="checkAvailability" required
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg py-3 px-4 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Usulan Ruangan *</label>
                                <input type="text" name="ruangan_usulan" x-model="proposedRoom" @input.debounce.500ms="checkAvailability" required 
                                    list="roomSuggestions" placeholder="Ketik nama ruangan atau pilih dari daftar..."
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg py-3 px-4">
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
                        
                        <!-- Availability Status Box -->
                        <div x-show="selectedDate" x-transition.opacity class="mb-6 p-5 rounded-2xl border transition-all" :class="statusColor">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-0.5 text-xl" x-text="statusIcon"></div>
                                <div class="ml-3 w-full">
                                    <h3 class="text-sm font-extrabold uppercase tracking-wide" :class="statusTextColor" x-text="statusTitle"></h3>
                                    <div class="mt-1 text-sm font-medium" :class="statusTextColor" x-html="statusMessage"></div>
                                    
                                    <div class="mt-3 pt-3 border-t border-current/10 flex flex-wrap gap-2 items-center text-xs" :class="statusTextColor">
                                        <span class="font-bold uppercase tracking-wider opacity-75">Detail Verifikasi:</span>
                                        <span class="bg-black/5 px-2.5 py-1 rounded-md font-bold" x-text="selectedDate"></span>
                                        <span class="bg-black/5 px-2.5 py-1 rounded-md font-bold" x-text="startTime + ' - ' + endTime"></span>
                                        <span class="bg-black/5 px-2.5 py-1 rounded-md font-black" x-text="'Ruang: ' + (proposedRoom || 'Belum diisi') + (proposedRoomType ? ' (' + proposedRoomType + ')' : '')"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Jam Mulai *</label>
                                <input type="time" name="waktu_mulai_time" x-model="startTime" @change="checkAvailability" required
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg py-3 px-4">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Jam Selesai *</label>
                                <input type="time" name="waktu_selesai_time" x-model="endTime" @change="checkAvailability" required
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg py-3 px-4">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Mode Pengajaran *</label>
                                <select name="is_online" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg py-3 px-4">
                                    <option value="0">Tatap Muka (Offline)</option>
                                    <option value="1">Online</option>
                                </select>
                            </div>
                        </div>
                        
                        <input type="hidden" name="waktu_mulai_usulan_date" :value="selectedDate">
                        <input type="hidden" name="waktu_mulai_usulan_time" :value="startTime">
                        <input type="hidden" name="waktu_selesai_usulan_time" :value="endTime">

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Pergantian</label>
                            <textarea name="alasan" rows="4" required placeholder="Contoh: Menghadiri seminar di luar kota..."
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-md p-4"></textarea>
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <button type="submit" :disabled="isBlocked"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 border border-transparent rounded-xl font-semibold text-white tracking-widest hover:from-indigo-700 hover:to-blue-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150 shadow-lg">
                                Ajukan Permohonan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </div>

    <script>
        function scheduleRequest() {
            return {
                selectedDate: '{{ request("date") ? \Carbon\Carbon::parse(request("date"))->format("Y-m-d") : "" }}',
                startTime: '{{ request("date") ? \Carbon\Carbon::parse(request("date"))->format("H:i") : "08:00" }}',
                endTime: '{{ request("date") ? \Carbon\Carbon::parse(request("date"))->copy()->addHours(2)->format("H:i") : "10:00" }}',
                proposedRoom: '{{ request("room") ?? "" }}',
                proposedRoomType: '',
                pengajuType: 'dosen',
                substituteId: '',
                isBlocked: false,
                statusColor: 'bg-gray-50 border-gray-200',
                statusTextColor: 'text-gray-800',
                statusTitle: 'Pilih tanggal',
                statusMessage: 'Silakan pilih tanggal untuk mengecek ketersediaan jadwal.',
                statusIcon: '📅',
                
                init() {
                    if (this.selectedDate) this.checkAvailability();
                },
                
                async checkAvailability() {
                    if (!this.selectedDate || !this.startTime || !this.endTime) return;
                    
                    let dateObj = new Date(this.selectedDate);
                    if (dateObj.getDay() === 0) {
                        this.isBlocked = true;
                        this.statusColor = 'bg-red-50 border-red-200';
                        this.statusTextColor = 'text-red-800';
                        this.statusTitle = 'Hari Minggu Tidak Berlaku';
                        this.statusMessage = 'Pengajuan pergantian jadwal hanya dapat dilakukan untuk hari Senin hingga Sabtu.';
                        this.statusIcon = '❌';
                        this.proposedRoomType = '';
                        return;
                    }
                    
                    this.statusTitle = 'Mengecek ketersediaan...';
                    
                    try {
                        const response = await fetch(`/api/availability?date=${this.selectedDate}&start_time=${this.startTime}&end_time=${this.endTime}&dosen_id={{ $schedule->user_id }}&dosen_pengganti_id=${this.substituteId}&schedule_id={{ $schedule->id }}&room=${encodeURIComponent(this.proposedRoom)}`);
                        const data = await response.json();
                        
                        this.proposedRoomType = data.room_details ? data.room_details.type : '';
                        
                        if (data.dosen_schedule_conflict) {
                            this.isBlocked = true;
                            this.statusColor = 'bg-red-50 border-red-200';
                            this.statusTextColor = 'text-red-800';
                            this.statusTitle = '⚠️ Jadwal / Kuota Dosen Bentrok!';
                            this.statusMessage = data.message.replace(/\n/g, '<br>') || 'Dosen memiliki jadwal mengajar reguler lain atau melebihi kuota harian pada jam terpilih.';
                            this.statusIcon = '❌';
                        } else if (data.dosen_request_conflict) {
                            this.isBlocked = true;
                            this.statusColor = 'bg-red-50 border-red-200';
                            this.statusTextColor = 'text-red-800';
                            this.statusTitle = '⚠️ Permohonan Bentrok!';
                            this.statusMessage = data.message.replace(/\n/g, '<br>') || 'Dosen memiliki permohonan pengganti lain pada jam terpilih.';
                            this.statusIcon = '❌';
                        } else if (data.room_conflict) {
                            this.isBlocked = true;
                            this.statusColor = 'bg-orange-50 border-orange-200';
                            this.statusTextColor = 'text-orange-850';
                            this.statusTitle = '❌ Ruangan Sudah Digunakan / Bentrok!';
                            this.statusMessage = `Ruangan ini sedang terpakai oleh: <strong>${data.room_conflict.dosen}</strong> (${data.room_conflict.mata_kuliah} - ${data.room_conflict.kelas}) pada jam ${data.room_conflict.waktu}.`;
                            this.statusIcon = '🔒';
                        } else {
                            this.isBlocked = false;
                            this.statusColor = 'bg-green-50 border-green-200';
                            this.statusTextColor = 'text-green-800';
                            this.statusTitle = '✓ Hari Kosong / Ruangan Tersedia';
                            this.statusMessage = 'Jadwal dan ruangan tersedia untuk waktu yang diusulkan.';
                            this.statusIcon = '✅';
                        }
                    } catch (e) {
                        console.error("Failed to check availability");
                    }
                }
            }
        }
    </script>
</x-guest-layout>
