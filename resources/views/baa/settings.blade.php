<x-app-layout>
<x-slot name="header"><h2 class="font-bold text-xl text-gray-800">BAA — Pengaturan Sistem</h2></x-slot>

<div class="py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-2xl">{{ session('success') }}</div>
        @endif

        {{-- SLA Setting --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-base font-bold text-gray-800 mb-4">⏱ Pengaturan SLA Persetujuan</h3>
            <p class="text-sm text-gray-500 mb-4">SLA menentukan batas waktu (dalam jam) untuk Kaprodi menyetujui/menolak permohonan. Setelah melebihi waktu ini, permohonan akan ditandai sebagai "breach".</p>
            <form method="POST" action="{{ route('baa.settings.update') }}" class="flex items-end gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Batas Waktu (Jam)</label>
                    <input type="number" name="jam_sla" value="{{ $sla->jam_sla ?? 48 }}" min="1" max="720" required
                        class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm w-36 focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all text-sm">Simpan SLA</button>
            </form>
        </div>

        {{-- Honor Dosen Setting --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-base font-bold text-gray-800 mb-4">💰 Pengaturan Honor Dosen</h3>
            <p class="text-sm text-gray-500 mb-4">Set tarif honor per jam mengajar untuk setiap dosen. Ini akan digunakan untuk menghitung total honor saat mencatat presensi.</p>
            <form method="POST" action="{{ route('baa.dosen.honor') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Pilih Dosen</label>
                        <select name="dosen_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih Dosen --</option>
                            @foreach($dosens as $d)
                            <option value="{{ $d->id }}">{{ $d->name }} (Rp {{ number_format($d->honor_per_jam ?? 0, 0, ',', '.') }}/jam)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">NIDN</label>
                        <input type="text" name="nidn" placeholder="0401019001" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Honor per Jam (Rp)</label>
                        <input type="number" name="honor_per_jam" placeholder="150000" min="0" step="1000" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition-all text-sm">Simpan Honor</button>
            </form>
        </div>

        {{-- Room Management --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6" x-data="{showForm:false}">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-800">🏫 Master Ruangan</h3>
                <button @click="showForm=!showForm" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all">+ Tambah Ruangan</button>
            </div>

            {{-- Add Room Form --}}
            <div x-show="showForm" x-transition class="mb-5 p-5 bg-indigo-50 rounded-2xl border border-indigo-100">
                <form method="POST" action="{{ route('baa.rooms.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Nama Ruangan</label>
                        <input type="text" name="name" required placeholder="Lab Komputer 1" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Tipe</label>
                        <select name="type" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="kelas">Kelas</option>
                            <option value="lab">Lab</option>
                            <option value="aula">Aula</option>
                            <option value="online">Online</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Kapasitas</label>
                        <input type="number" name="capacity" placeholder="40" min="1" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all">Tambah</button>
                    </div>
                </form>
            </div>

            {{-- Rooms Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-widest">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Tipe</th>
                            <th class="px-4 py-3 text-left">Kapasitas</th>
                            <th class="px-4 py-3 text-center">Aktif</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($rooms as $room)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-800">{{ $room->name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold
                                    {{ $room->type==='lab' ? 'bg-purple-100 text-purple-700' :
                                       ($room->type==='aula' ? 'bg-amber-100 text-amber-700' :
                                        ($room->type==='online' ? 'bg-cyan-100 text-cyan-700' : 'bg-gray-100 text-gray-600')) }}">
                                    {{ ucfirst($room->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $room->capacity ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="{{ $room->is_active ? 'text-green-600' : 'text-red-400' }} font-bold text-xs">{{ $room->is_active ? '✓ Aktif' : '✕' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('baa.rooms.destroy', $room) }}" onsubmit="return confirm('Hapus ruangan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-bold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
