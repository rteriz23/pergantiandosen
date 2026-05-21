<x-guest-layout>
    @include('layouts.public-navigation')
    
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-black text-2xl text-gray-800 mb-6">Kalender Akademik</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($kalenders as $kalender)
                    <div class="border border-gray-200 rounded-2xl p-5 hover:shadow-md transition">
                        <div class="text-sm font-bold text-indigo-600 mb-2">
                            {{ \Carbon\Carbon::parse($kalender->tanggal_mulai)->format('d M Y') }} 
                            @if($kalender->tanggal_selesai && $kalender->tanggal_selesai != $kalender->tanggal_mulai)
                                - {{ \Carbon\Carbon::parse($kalender->tanggal_selesai)->format('d M Y') }}
                            @endif
                        </div>
                        <h3 class="text-lg font-black text-gray-800">{{ $kalender->nama }}</h3>
                        <p class="text-sm text-gray-500 mt-2">{{ $kalender->keterangan }}</p>
                    </div>
                    @empty
                    <div class="col-span-full py-10 text-center text-gray-500">
                        Belum ada data Kalender Akademik.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
