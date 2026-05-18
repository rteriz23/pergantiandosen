
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($room) ? 'Edit' : 'Tambah' }} Room
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-8 border border-gray-100 max-w-2xl mx-auto">
                <form action="{{ isset($room) ? route('admin.room.update', $room->id) : route('admin.room.store') }}" method="POST">
                    @csrf
                    @if(isset($room))
                        @method('PUT')
                    @endif

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Ruangan</label>
                        <input type="text" name="name" value="{{ old('name', $room->name ?? '') }}" required class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tipe</label>
                        <select name="type" required class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                            <option value="kelas" {{ (old('type', $room->type ?? '') == 'kelas') ? 'selected' : '' }}>Kelas</option>
                            <option value="lab" {{ (old('type', $room->type ?? '') == 'lab') ? 'selected' : '' }}>Laboratorium</option>
                            <option value="aula" {{ (old('type', $room->type ?? '') == 'aula') ? 'selected' : '' }}>Aula / Teater</option>
                            <option value="online" {{ (old('type', $room->type ?? '') == 'online') ? 'selected' : '' }}>Online (Virtual)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Kapasitas</label>
                        <input type="number" name="capacity" value="{{ old('capacity', $room->capacity ?? '') }}" class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Keterangan</label>
                        <textarea name="keterangan" rows="3" class="shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500">{{ old('keterangan', $room->keterangan ?? '') }}</textarea>
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $room->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">Aktif</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end mt-8">
                        <a href="{{ route('admin.room.index') }}" class="text-gray-500 hover:text-gray-700 mr-4 font-medium">Batal</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>