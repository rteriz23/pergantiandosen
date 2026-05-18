<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kelola Mahasiswa
            </h2>
            <a href="{{ route('admin.mahasiswa.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm font-medium transition">Tambah Mahasiswa</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg" role="alert">
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Filter & Import Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Filter Section -->
                <div class="lg:col-span-2 bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z"></path></svg>
                        Filter Pencarian
                    </h3>
                    <form action="{{ route('admin.mahasiswa.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Cari Nama / NIM / Email</label>
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Masukkan kata kunci..." class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Program Studi</label>
                            <select name="prodi_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-sm">
                                <option value="">Semua Prodi</option>
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ ($prodiId ?? '') == $prodi->id ? 'selected' : '' }}>{{ $prodi->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3 flex justify-end space-x-2">
                            @if($search || $prodiId)
                                <a href="{{ route('admin.mahasiswa.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium transition">Reset</a>
                            @endif
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Terapkan Filter</button>
                        </div>
                    </form>
                </div>

                <!-- Import Section -->
                <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Import CSV
                    </h3>
                    <form action="{{ route('admin.mahasiswa.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500 mb-2">Upload file CSV mahasiswa dengan format kolom: <br><strong class="text-gray-700 font-bold">name, nim, email, prodi_name, kelas_name</strong></label>
                            <input type="file" name="csv_file" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Mulai Import</button>
                    </form>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden p-6 border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-4 border-b-2 border-gray-100 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">ID</th>
                                <th class="px-5 py-4 border-b-2 border-gray-100 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nama & Email</th>
                                <th class="px-5 py-4 border-b-2 border-gray-100 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">NIM</th>
                                <th class="px-5 py-4 border-b-2 border-gray-100 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Kelas</th>
                                <th class="px-5 py-4 border-b-2 border-gray-100 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Program Studi</th>
                                <th class="px-5 py-4 border-b-2 border-gray-100 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($mahasiswas as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-4 bg-white text-sm text-gray-700">{{ $item->id }}</td>
                                    <td class="px-5 py-4 bg-white text-sm font-medium text-gray-900">
                                        <div class="font-bold">{{ $item->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->email }}</div>
                                    </td>
                                    <td class="px-5 py-4 bg-white text-sm text-gray-700 font-bold">
                                        {{ $item->nim }}
                                    </td>
                                    <td class="px-5 py-4 bg-white text-sm text-gray-700">
                                        <span class="px-2 py-0.5 rounded bg-gray-100 font-bold text-gray-800 text-xs">{{ $item->kelas }}</span>
                                    </td>
                                    <td class="px-5 py-4 bg-white text-sm font-medium text-gray-900">
                                        {{ $item->prodi->name ?? '-' }}
                                    </td>
                                    <td class="px-5 py-4 bg-white text-sm">
                                        <div class="flex space-x-3">
                                            <a href="{{ route('admin.mahasiswa.edit', $item->id) }}" class="text-blue-600 hover:text-blue-900 font-medium transition">Edit</a>
                                            <form action="{{ route('admin.mahasiswa.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus mahasiswa ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium transition">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $mahasiswas->links() }}
                </div>
                @if(count($mahasiswas) == 0)
                    <div class="text-center py-12 text-gray-500">
                        Belum ada data mahasiswa ditemukan.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>