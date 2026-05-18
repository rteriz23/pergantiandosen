
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($dosen) ? 'Edit' : 'Tambah' }} Dosen
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-8 border border-gray-100 max-w-2xl mx-auto">
                <form action="{{ isset($dosen) ? route('admin.dosen.update', $dosen->id) : route('admin.dosen.store') }}" method="POST">
                    @csrf
                    @if(isset($dosen))
                        @method('PUT')
                    @endif

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama / Label / Judul / Kode</label>
                        <input type="text" name="name" value="{{ $dosen->name ?? $dosen->nama ?? $dosen->nama_kelas ?? $dosen->kode_ruangan ?? $dosen->judul ?? '' }}" class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <input type="email" name="email" value="{{ $dosen->email ?? '' }}" class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Program Studi</label>
                        <select name="prodi_id" class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Program Studi (Opsional) --</option>
                            @foreach($prodis as $prodi)
                                <option value="{{ $prodi->id }}" {{ (isset($dosen) && $dosen->prodi_id == $prodi->id) ? 'selected' : '' }}>{{ $prodi->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end mt-8">
                        <a href="{{ route('admin.dosen.index') }}" class="text-gray-500 hover:text-gray-700 mr-4 font-medium">Batal</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>