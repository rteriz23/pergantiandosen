@extends('layouts.app')
@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-2xl">{{ session('success') }}</div>
        @endif

        {{-- Global Setting --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-base font-bold text-gray-800 mb-1">Batas Pengajuan Pergantian Mahasiswa</h3>
            <p class="text-sm text-gray-500 mb-4">Konfigurasi berapa kali maksimum seorang mahasiswa dapat mengajukan pergantian jadwal per semester. Anda bisa set global atau per-prodi.</p>

            <form method="POST" action="{{ route('kemahasiswaan.settings.update') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Program Studi (kosong = global)</label>
                    <select name="prodi_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Global (semua prodi) —</option>
                        @foreach($prodis as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Maks. Pergantian</label>
                    <input type="number" name="max_pergantian" value="{{ $global->max_pergantian ?? 3 }}" min="1" max="20" required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all">Simpan</button>
                </div>
            </form>

            {{-- Current Settings Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm mt-4">
                    <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-widest">
                        <tr>
                            <th class="px-4 py-3 text-left">Prodi</th>
                            <th class="px-4 py-3 text-center">Maks. Pergantian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($prodis as $p)
                        @php $s = $settings[$p->id] ?? null; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $p->name }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($s)
                                <span class="font-black text-indigo-700">{{ $s->max_pergantian }}x</span>
                                @else
                                <span class="text-gray-400 text-xs">Global ({{ $global->max_pergantian ?? 3 }}x)</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
