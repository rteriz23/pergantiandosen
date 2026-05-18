
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($mahasiswa) ? 'Edit' : 'Tambah' }} Mahasiswa
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-8 border border-gray-100 max-w-2xl mx-auto">
                <form action="{{ isset($mahasiswa) ? route('admin.mahasiswa.update', $mahasiswa->id) : route('admin.mahasiswa.store') }}" method="POST">
                    @csrf
                    @if(isset($mahasiswa))
                        @method('PUT')
                    @endif

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama / Label / Judul / Kode</label>
                        <input type="text" name="nama" value="{{ $mahasiswa->name ?? $mahasiswa->nama ?? $mahasiswa->nama_kelas ?? $mahasiswa->kode_ruangan ?? $mahasiswa->judul ?? '' }}" class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    

                    <div class="flex items-center justify-end mt-8">
                        <a href="{{ route('admin.mahasiswa.index') }}" class="text-gray-500 hover:text-gray-700 mr-4 font-medium">Batal</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>