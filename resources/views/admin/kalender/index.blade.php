<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kelola Kalender Akademik
            </h2>
            <a href="{{ route('admin.kalender.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm font-medium transition">Tambah Agenda</a>
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
                    <form action="{{ route('admin.kalender.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Cari Agenda / Keterangan</label>
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Masukkan nama agenda..." class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-sm">
                        </div>
                        <div class="flex items-end space-x-2">
                            @if($search)
                                <a href="{{ route('admin.kalender.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium transition">Reset</a>
                            @endif
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Terapkan</button>
                            <a href="{{ route('admin.kalender.export', request()->query()) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Export
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Import Section -->
                <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Import CSV
                    </h3>
                    <form action="{{ route('admin.kalender.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500 mb-2">Upload CSV agenda kalender dengan format kolom: <br><strong class="text-gray-700 font-bold">judul, tanggal_mulai, tanggal_selesai, keterangan, warna</strong></label>
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
                                <th class="px-5 py-4 border-b-2 border-gray-100 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Agenda</th>
                                <th class="px-5 py-4 border-b-2 border-gray-100 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Mulai</th>
                                <th class="px-5 py-4 border-b-2 border-gray-100 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Selesai</th>
                                <th class="px-5 py-4 border-b-2 border-gray-100 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($kalenders as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-4 bg-white text-sm text-gray-700">{{ $item->id }}</td>
                                    <td class="px-5 py-4 bg-white text-sm font-bold text-gray-900 flex items-center">
                                        <span class="w-3.5 h-3.5 rounded-full mr-2" style="background-color: {{ $item->warna ?? '#4F46E5' }}"></span>
                                        <div>
                                            <div class="font-bold text-gray-900">{{ $item->judul }}</div>
                                            <div class="text-xs text-gray-500 font-medium italic mt-0.5">{{ $item->keterangan ?? '-' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 bg-white text-sm text-gray-700 font-medium">
                                        {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }}
                                    </td>
                                    <td class="px-5 py-4 bg-white text-sm text-gray-700 font-medium">
                                        {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y') }}
                                    </td>
                                    <td class="px-5 py-4 bg-white text-sm">
                                        <div class="flex space-x-3">
                                            <a href="{{ route('admin.kalender.edit', $item->id) }}" class="text-blue-600 hover:text-blue-900 font-medium transition">Edit</a>
                                            <form action="{{ route('admin.kalender.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus agenda ini?');" class="inline">
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
                    {{ $kalenders->links() }}
                </div>
                @if(count($kalenders) == 0)
                    <div class="text-center py-12 text-gray-500">
                        Belum ada data agenda kalender akademik ditemukan.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>