<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Jadwal Kuliah
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-8 border border-gray-100 max-w-2xl mx-auto">
                
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                        <div class="text-sm font-bold text-red-700 mb-1">Ada beberapa kesalahan input:</div>
                        <ul class="list-disc pl-5 text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.schedule.update', $schedule->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Dosen -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Dosen Pengajar</label>
                        <select name="user_id" required class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Dosen --</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('user_id', $schedule->user_id) == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->name }} ({{ $dosen->prodi->name ?? 'Prodi N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Mata Kuliah -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Mata Kuliah</label>
                        <select name="matakuliah_id" required class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Mata Kuliah --</option>
                            @foreach($matakuliahs as $mk)
                                <option value="{{ $mk->id }}" {{ old('matakuliah_id', $matchedMk->id ?? '') == $mk->id ? 'selected' : '' }}>
                                    {{ $mk->kode }} - {{ $mk->nama }} ({{ $mk->sks }} SKS)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kelas -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Kelas</label>
                        <select name="kelas_id" required class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $item)
                                <option value="{{ $item->id }}" {{ old('kelas_id', $matchedKls->id ?? '') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Ruangan -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Ruangan (Opsional)</label>
                        <select name="room_id" class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Belum Ditentukan / TBA --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('room_id', $schedule->room_id) == $room->id ? 'selected' : '' }}>
                                    {{ $room->name }} (Kapasitas: {{ $room->capacity ?? '-' }} - Tipe: {{ strtoupper($room->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Waktu Pertemuan -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal</label>
                            <input type="date" name="tanggal" value="{{ old('tanggal', $tanggal) }}" required class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jam Mulai</label>
                            <input type="time" name="waktu_mulai_time" value="{{ old('waktu_mulai_time', $startTime) }}" required class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jam Selesai</label>
                            <input type="time" name="waktu_selesai_time" value="{{ old('waktu_selesai_time', $endTime) }}" required class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Informasi Tambahan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Periode</label>
                            <select name="periode" required class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                                @foreach($activePeriodes as $period)
                                    <option value="{{ $period }}" {{ old('periode', $schedule->periode) == $period ? 'selected' : '' }}>{{ $period }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pertemuan Ke-</label>
                            <input type="number" name="pertemuan" value="{{ old('pertemuan', $schedule->pertemuan) }}" min="1" max="16" required class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8">
                        <a href="{{ route('admin.schedule.index') }}" class="text-gray-500 hover:text-gray-700 mr-4 font-medium">Batal</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
