<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kelola Periode & Tahun Ajaran
            </h2>
            <button @click="showModal = true; isEdit = false; name = ''; actionUrl = '{{ route('baa.periodes.store') }}'" 
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-xl transition duration-150 shadow-sm">
                + Tambah Periode Baru
            </button>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ showModal: false, actionUrl: '', name: '', is_active: true, isEdit: false }">
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
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z"></path></svg>
                        Filter Pencarian
                    </h3>
                    <form action="{{ route('baa.periodes.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Cari Nama Periode / Tahun Ajaran</label>
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Contoh: 2026 Ganjil" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 text-sm">
                        </div>
                        <div class="flex items-end space-x-2">
                            @if($search)
                                <a href="{{ route('baa.periodes.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium transition">Reset</a>
                            @endif
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Terapkan</button>
                        </div>
                    </form>
                </div>

                <!-- Import Section -->
                <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Import CSV
                    </h3>
                    <form action="{{ route('baa.periodes.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500 mb-2">Upload file CSV periode dengan format kolom: <br><strong class="text-gray-700 font-bold">name</strong></label>
                            <input type="file" name="csv_file" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Mulai Import</button>
                    </form>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Periode</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($periodes as $p)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">{{ $p->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($p->is_active)
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800 border border-green-200">Aktif</span>
                                        @else
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-bold rounded-full bg-gray-100 text-gray-800 border border-gray-200">Non-Aktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                        <button @click="showModal = true; isEdit = true; name = '{{ $p->name }}'; is_active = {{ $p->is_active ? 'true' : 'false' }}; actionUrl = '{{ route('baa.periodes.update', $p->id) }}'" 
                                            class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded-md mr-2 transition duration-150 font-bold">Edit</button>
                                        
                                        <form action="{{ route('baa.periodes.destroy', $p->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus periode ini?')" class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1 rounded-md transition duration-150 font-bold">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Belum ada periode yang didaftarkan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="showModal" x-transition.scale class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form :action="actionUrl" method="POST">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4" x-text="isEdit ? 'Edit Periode' : 'Tambah Periode Baru'"></h3>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Periode / Tahun Ajaran</label>
                                <input type="text" name="name" x-model="name" required placeholder="Contoh: 2026 Ganjil" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" x-model="is_active" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label class="ml-2 block text-sm text-gray-900 font-medium">Aktifkan di Kalender Publik</label>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm font-bold" x-text="isEdit ? 'Simpan Perubahan' : 'Tambah'"></button>
                            <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm font-bold">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
