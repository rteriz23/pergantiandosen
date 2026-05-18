@extends('layouts.app')
@section('content')
<div class="py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Data Mahasiswa & Riwayat Pengajuan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-widest">
                        <tr>
                            <th class="px-5 py-3 text-left">NIM</th>
                            <th class="px-5 py-3 text-left">Nama</th>
                            <th class="px-5 py-3 text-left">Prodi</th>
                            <th class="px-5 py-3 text-center">Pengajuan</th>
                            <th class="px-5 py-3 text-center">Kuota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($mahasiswas as $m)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $m->nim }}</td>
                            <td class="px-5 py-3 font-semibold text-gray-800">{{ $m->nama }}</td>
                            <td class="px-5 py-3 text-gray-500 text-xs">{{ $m->prodi->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-20 bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $m->submission_count >= $m->max_pergantian ? 'bg-red-500' : 'bg-indigo-500' }}"
                                            style="width: {{ min(100, ($m->submission_count / max(1,$m->max_pergantian)) * 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold {{ $m->submission_count >= $m->max_pergantian ? 'text-red-600' : 'text-indigo-700' }}">
                                        {{ $m->submission_count }}/{{ $m->max_pergantian }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($m->submission_count >= $m->max_pergantian)
                                <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">Penuh</span>
                                @else
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Tersedia</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">Belum ada data mahasiswa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
