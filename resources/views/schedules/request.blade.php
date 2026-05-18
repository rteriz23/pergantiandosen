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
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Pilih Jadwal Pengganti</h3>
                    
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
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Usulan</label>
                            <input type="date" x-model="selectedDate" @change="checkAvailability"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg py-3 px-4 transition-colors">
                        </div>
                        
                        <!-- Availability Status Box -->
                        <div x-show="selectedDate" x-transition.opacity class="mb-6 p-5 rounded-xl border" :class="statusColor">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-0.5" x-html="statusIcon"></div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium" :class="statusTextColor" x-text="statusTitle"></h3>
                                    <div class="mt-1 text-sm" :class="statusTextColor" x-html="statusMessage"></div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai</label>
                                <input type="time" name="waktu_mulai_time" x-model="startTime" required
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg py-3 px-4">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jam Selesai</label>
                                <input type="time" name="waktu_selesai_time" x-model="endTime" required
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg py-3 px-4">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Usulan Ruangan</label>
                                <input type="text" name="ruangan_usulan" x-model="proposedRoom" @input.debounce.500ms="checkAvailability" required placeholder="Contoh: Lab 2 / Kelas A"
                                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg py-3 px-4">
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
                selectedDate: '',
                startTime: '08:00',
                endTime: '10:00',
                proposedRoom: '',
                isBlocked: false,
                statusColor: 'bg-gray-50 border-gray-200',
                statusTextColor: 'text-gray-800',
                statusTitle: 'Pilih tanggal',
                statusMessage: 'Silakan pilih tanggal untuk mengecek ketersediaan jadwal.',
                statusIcon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
                
                async checkAvailability() {
                    if (!this.selectedDate) return;
                    
                    let dateObj = new Date(this.selectedDate);
                    if (dateObj.getDay() === 0) {
                        this.isBlocked = true;
                        this.statusColor = 'bg-red-50 border-red-200';
                        this.statusTextColor = 'text-red-800';
                        this.statusTitle = 'Hari Minggu Tidak Berlaku';
                        this.statusMessage = 'Pengajuan pergantian jadwal hanya dapat dilakukan untuk hari Senin hingga Sabtu.';
                        this.statusIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                        return;
                    }
                    
                    this.statusTitle = 'Mengecek ketersediaan...';
                    
                    try {
                        const response = await fetch(`/api/availability?date=${this.selectedDate}&dosen_id={{ $schedule->user_id }}&room=${encodeURIComponent(this.proposedRoom)}`);
                        const data = await response.json();
                        
                        if (data.is_holiday) {
                            this.isBlocked = true;
                            this.statusColor = 'bg-red-50 border-red-200';
                            this.statusTextColor = 'text-red-800';
                            this.statusTitle = 'Tanggal Merah / Libur Nasional';
                            this.statusMessage = `<strong>${data.holiday_desc}</strong>. Anda tidak dapat mengajukan jadwal pengganti pada tanggal ini.`;
                            this.statusIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                        } else if (data.schedules.length > 0) {
                            this.isBlocked = false; 
                            this.statusColor = 'bg-yellow-50 border-yellow-200';
                            this.statusTextColor = 'text-yellow-800';
                            this.statusTitle = 'Terdapat jadwal lain di hari ini';
                            
                            let html = '<ul class="list-disc pl-4 mt-2 space-y-1">';
                            data.schedules.forEach(s => {
                                let start = new Date(s.waktu_mulai).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
                                let end = new Date(s.waktu_selesai).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
                                html += `<li>${s.mata_kuliah} (${start} - ${end})</li>`;
                            });
                            html += '</ul><p class="mt-2 text-xs">Pastikan jam usulan Anda tidak berbenturan dengan jadwal di atas.</p>';
                            this.statusMessage = html;
                            this.statusIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
                        } else if (data.room_conflicts && data.room_conflicts.length > 0) {
                            this.isBlocked = false;
                            this.statusColor = 'bg-orange-50 border-orange-200';
                            this.statusTextColor = 'text-orange-800';
                            this.statusTitle = 'Ruangan Sudah Digunakan';
                            this.statusMessage = 'Ruangan ini sudah digunakan oleh jadwal lain di hari yang sama. Silakan pilih ruangan lain.';
                            this.statusIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>';
                        } else {
                            this.isBlocked = false;
                            this.statusColor = 'bg-green-50 border-green-200';
                            this.statusTextColor = 'text-green-800';
                            this.statusTitle = 'Hari Kosong / Tersedia';
                            this.statusMessage = 'Tidak ada jadwal mengajar pada hari ini. Sangat cocok untuk jadwal pengganti.';
                            this.statusIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                        }
                    } catch (e) {
                        console.error("Failed to check availability");
                    }
                }
            }
        }
    </script>
</x-guest-layout>
