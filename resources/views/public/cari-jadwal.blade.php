<x-guest-layout>
    @include('layouts.public-navigation')
    
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Form Card (Glassmorphism & premium feel) -->
            <div class="bg-white/80 backdrop-blur-md rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/50 p-8 mb-8"
                 x-data="{
                     dosenId: '{{ request('dosen_id') ?? '' }}',
                     schedules: [],
                     selectedScheduleId: '',
                     loadingSchedules: false,
                     init() {
                         if (this.dosenId) {
                             this.loadSchedules();
                         }
                     },
                     loadSchedules() {
                         if (!this.dosenId) {
                             this.schedules = [];
                             this.selectedScheduleId = '';
                             return;
                         }
                         this.loadingSchedules = true;
                         fetch('/api/dosen/' + this.dosenId + '/schedules')
                             .then(res => res.json())
                             .then(data => {
                                 this.schedules = data;
                                 this.loadingSchedules = false;
                             })
                             .catch(err => {
                                 console.error(err);
                                 this.loadingSchedules = false;
                             });
                     },
                     applySchedule(schedId) {
                         const sched = this.schedules.find(s => s.id == schedId);
                         if (sched) {
                             document.getElementById('input-date').value = sched.tanggal;
                             document.getElementById('input-start-time').value = sched.jam_mulai;
                             document.getElementById('input-end-time').value = sched.jam_selesai;
                         }
                     }
                 }">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="bg-indigo-100 text-indigo-700 p-2.5 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <div>
                        <h2 class="font-black text-2xl text-gray-900 tracking-tight">Pencarian Ruangan Kosong</h2>
                        <p class="text-xs text-gray-500 font-semibold mt-0.5 uppercase tracking-wider">Cari Ketersediaan Ruang Kelas LPKIA</p>
                    </div>
                </div>

                <form action="{{ route('public.cari_jadwal_kosong') }}" method="GET" class="space-y-6">
                    <!-- Row 1: Lecturer & Schedule Selection -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Select Dosen -->
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Pilih Dosen (Filter)</label>
                            <div class="relative">
                                <select name="dosen_id" x-model="dosenId" @change="loadSchedules()" class="appearance-none w-full bg-white border border-gray-200/80 rounded-2xl pl-5 pr-10 py-3.5 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all hover:bg-gray-50 cursor-pointer shadow-sm">
                                    <option value="">-- Pilih Dosen (Opsional) --</option>
                                    @foreach($dosens as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-indigo-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Select Schedule (Dynamic) -->
                        <div x-show="dosenId" style="display: none;">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
                                Pilih Jadwal Kuliah Asli
                                <template x-if="loadingSchedules">
                                    <span class="text-[10px] text-indigo-500 font-bold ml-1 animate-pulse">(Memuat...)</span>
                                </template>
                            </label>
                            <div class="relative">
                                <select x-model="selectedScheduleId" @change="applySchedule($event.target.value)" class="appearance-none w-full bg-white border border-gray-200/80 rounded-2xl pl-5 pr-10 py-3.5 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all hover:bg-gray-50 cursor-pointer shadow-sm">
                                    <option value="">-- Pilih Jadwal Kuliah untuk Pre-fill Waktu --</option>
                                    <template x-for="s in schedules" :key="s.id">
                                        <option :value="s.id" x-text="s.details"></option>
                                    </template>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-indigo-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Date & Time Slot Parameters -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Tanggal</label>
                            <input type="date" id="input-date" name="date" value="{{ $date }}" required class="w-full bg-white border border-gray-200/80 rounded-2xl px-5 py-3 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Jam Mulai</label>
                            <input type="time" id="input-start-time" name="start_time" value="{{ $startTime ?? '07:00' }}" required class="w-full bg-white border border-gray-200/80 rounded-2xl px-5 py-3 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Jam Selesai</label>
                            <input type="time" id="input-end-time" name="end_time" value="{{ $endTime ?? '18:00' }}" required class="w-full bg-white border border-gray-200/80 rounded-2xl px-5 py-3 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        </div>
                        <div class="md:col-span-1">
                            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white py-3.5 rounded-2xl font-bold transition transform hover:-translate-y-0.5 shadow-lg shadow-indigo-500/25 flex items-center justify-center space-x-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <span>Cari Ruangan</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @if($date)
            <div class="space-y-8">
                @foreach($roomsStatus as $key => $category)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition duration-300 hover:shadow-md">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">{{ $category['icon'] }}</span>
                            <div>
                                <h3 class="text-lg font-black text-gray-900 font-bold tracking-tight">
                                    {{ $category['title'] }}
                                </h3>
                                <p class="text-xs text-gray-500 font-medium">{{ $category['description'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-xs font-bold shrink-0">
                            <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-700">Total: {{ $category['total'] }}</span>
                            <span class="px-2.5 py-1 rounded-full bg-green-100 text-green-700">Kosong: {{ $category['available'] }}</span>
                            <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700">Bentrok: {{ $category['occupied'] }}</span>
                        </div>
                    </div>
                    <div class="p-6 bg-white">
                        @if(count($category['rooms']) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($category['rooms'] as $status)
                                    @if($status['is_available'])
                                        @php
                                            $dosenId = request('dosen_id');
                                            $params = [
                                                'date' => $date . ' ' . ($startTime ?? '07:30'),
                                                'room' => $status['room']->name,
                                                'end_time' => $endTime
                                            ];
                                            if ($dosenId) {
                                                $params['dosen_id'] = $dosenId;
                                            }
                                            $requestUrl = route('schedules.request_new', $params);
                                        @endphp
                                        <a href="{{ $requestUrl }}" class="border border-green-200 bg-gradient-to-br from-green-50/70 to-emerald-50/20 hover:from-green-100/80 hover:to-emerald-100/40 rounded-xl p-4 flex items-center justify-between transition-all hover:shadow-lg hover:border-green-400 cursor-pointer group relative overflow-hidden">
                                            <div class="absolute -right-6 -bottom-6 w-16 h-16 bg-green-500/5 rounded-full blur-xl group-hover:bg-green-500/10 transition-all"></div>
                                            <div class="relative z-10">
                                                <div class="font-black text-gray-900 font-bold flex items-center group-hover:text-green-950">
                                                    {{ $status['room']->name }}
                                                    <span class="text-[9px] font-bold bg-green-100 text-green-700 border border-green-200 px-1.5 py-0.5 rounded-md ml-2 uppercase">KOSONG</span>
                                                </div>
                                                <div class="text-xs font-semibold text-green-700 mt-1 flex items-center gap-1 group-hover:text-green-800">
                                                    <span>✓ Klik untuk Ajukan Ruangan</span>
                                                    <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                </div>
                                            </div>
                                            <div class="w-3 h-3 rounded-full bg-green-500 shadow-sm shadow-green-200 animate-pulse group-hover:scale-110 relative z-10"></div>
                                        </a>
                                    @else
                                        <div class="border border-red-200 bg-gradient-to-br from-red-50/60 to-rose-50/20 rounded-xl p-4 flex items-start justify-between transition hover:shadow-sm relative overflow-hidden">
                                            <div class="absolute -right-6 -bottom-6 w-16 h-16 bg-red-500/5 rounded-full blur-xl"></div>
                                            <div class="pr-2 relative z-10">
                                                <div class="font-black text-gray-900 font-bold flex items-center flex-wrap gap-1.5">
                                                    <span>{{ $status['room']->name }}</span>
                                                    <span class="text-[9px] font-bold bg-red-100 text-red-700 border border-red-200 px-1.5 py-0.5 rounded-md uppercase">{{ $status['occupied_by']['type'] }}</span>
                                                </div>
                                                <div class="text-[11px] font-bold text-red-800 mt-1.5 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>{{ $status['occupied_by']['waktu'] }}</span>
                                                </div>
                                                <div class="text-xs text-gray-700 mt-1 font-semibold leading-tight">
                                                    {{ $status['occupied_by']['dosen'] }}
                                                </div>
                                                <div class="text-[10px] text-gray-500 mt-0.5 font-medium leading-tight">
                                                    {{ $status['occupied_by']['mata_kuliah'] }} ({{ $status['occupied_by']['kelas'] }})
                                                </div>
                                            </div>
                                            <div class="w-3 h-3 rounded-full bg-red-500 shadow-sm shadow-red-200 mt-1 flex-shrink-0 relative z-10"></div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 text-gray-400 text-xs font-semibold">
                                Tidak ada ruangan aktif dalam kategori ini.
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <!-- Beautiful Onboarding Panel -->
            <div class="bg-white/85 backdrop-blur-md rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.03)] border border-white/60 p-10 text-center">
                <div class="w-20 h-20 bg-gradient-to-tr from-indigo-500 via-indigo-600 to-purple-600 rounded-3xl flex items-center justify-center mx-auto mb-6 text-white text-3xl shadow-xl shadow-indigo-500/20 transform hover:scale-105 transition duration-300">
                    🏛️
                </div>
                <h3 class="text-2xl font-black text-gray-900 tracking-tight">Mulai Pencarian Ruangan Kosong</h3>
                <p class="text-sm text-gray-500 max-w-lg mx-auto mt-3 leading-relaxed font-medium">
                    Pilih tanggal dan slot jam perkuliahan pada form di atas untuk memonitor ruangan mana saja yang sedang dipakai (bentrok) atau tersedia kosong untuk pergantian jadwal.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3 text-xs font-bold text-gray-500">
                    <span class="px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-full border border-gray-200/50 shadow-sm transition">🏢 Ruang R. 200 Series</span>
                    <span class="px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-full border border-gray-200/50 shadow-sm transition">🏫 Ruang R. 300 Series</span>
                    <span class="px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-full border border-gray-200/50 shadow-sm transition">💻 Laboratorium Komputer (Labkom)</span>
                    <span class="px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-full border border-gray-200/50 shadow-sm transition">🛡️ Pengecekan Bentrok Handal</span>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-guest-layout>
