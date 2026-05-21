<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Presensi & Honor Mengajar - {{ $presensi->dosen->name ?? 'Dosen' }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white;
                color: black;
                padding: 0;
                margin: 0;
            }
            .print-border {
                border-color: #000 !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900 antialiased p-6 sm:p-12">
    <div class="max-w-3xl mx-auto bg-white p-8 sm:p-12 rounded-2xl border border-gray-200 shadow-sm relative print-border">
        <!-- Floating Print Action for Screen -->
        <div class="no-print absolute top-6 right-6 flex gap-2">
            <button onclick="window.print()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Slip
            </button>
            <button onclick="window.close()" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl transition-all">
                Tutup
            </button>
        </div>

        <!-- LPKIA Letterhead -->
        <div class="border-b-4 border-indigo-900 pb-6 mb-6 flex flex-col md:flex-row items-center justify-between gap-4 print-border">
            <div class="text-center md:text-left">
                <h1 class="text-2xl font-black text-indigo-950 tracking-tight">INSTITUT LPKIA</h1>
                <p class="text-xs text-gray-500 font-semibold mt-0.5 uppercase tracking-wider">KBM & Academic Services Department</p>
                <p class="text-[10px] text-gray-400 mt-1">Jl. Soekarno Hatta No. 456, Bandung • Telp: (022) 7564256 • www.lpkia.ac.id</p>
            </div>
            <div class="text-right hidden md:block">
                <span class="px-4 py-1.5 bg-indigo-50 border border-indigo-200 text-indigo-800 rounded-xl text-xs font-bold uppercase tracking-widest print:border-black">
                    SLIP RESMI BAA
                </span>
            </div>
        </div>

        <!-- Title -->
        <div class="text-center my-6">
            <h2 class="text-xl font-bold text-gray-900 tracking-wide uppercase">Slip Presensi & Honor Mengajar Dosen</h2>
            <p class="text-xs text-gray-500 font-semibold mt-1">Kode Slip: SLIP-KBM-{{ str_pad($presensi->id, 5, '0', STR_PAD_LEFT) }}</p>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-8 bg-gray-50 p-6 rounded-2xl border border-gray-100 print:bg-white print:border-black">
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Informasi Dosen</h3>
                <table class="w-full text-sm">
                    <tr class="border-b border-gray-100 py-2">
                        <td class="text-gray-500 pr-4 font-medium py-1.5">Nama Dosen</td>
                        <td class="text-gray-900 font-bold py-1.5">: {{ $presensi->dosen->name ?? '-' }}</td>
                    </tr>
                    <tr class="border-b border-gray-100 py-2">
                        <td class="text-gray-500 pr-4 font-medium py-1.5">NIDN / ID</td>
                        <td class="text-gray-900 font-semibold py-1.5">: {{ $presensi->dosen->nidn ?? '-' }}</td>
                    </tr>
                    <tr class="py-2">
                        <td class="text-gray-500 pr-4 font-medium py-1.5">Program Studi</td>
                        <td class="text-gray-900 font-semibold py-1.5">: {{ $presensi->schedule->prodi->name ?? ($presensi->scheduleRequest->schedule->prodi->name ?? '-') }}</td>
                    </tr>
                </table>
            </div>
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Detail Kegiatan KBM</h3>
                <table class="w-full text-sm">
                    <tr class="border-b border-gray-100 py-2">
                        <td class="text-gray-500 pr-4 font-medium py-1.5">Mata Kuliah</td>
                        <td class="text-gray-900 font-bold py-1.5">: {{ $presensi->schedule->mata_kuliah ?? ($presensi->scheduleRequest->schedule->mata_kuliah ?? '-') }}</td>
                    </tr>
                    <tr class="border-b border-gray-100 py-2">
                        <td class="text-gray-500 pr-4 font-medium py-1.5">Kelas / Pert.</td>
                        <td class="text-gray-900 font-semibold py-1.5">: Kelas {{ $presensi->schedule->kelas ?? ($presensi->scheduleRequest->schedule->kelas ?? '-') }} • Pertemuan Ke-{{ $presensi->schedule->pertemuan ?? ($presensi->scheduleRequest->schedule->pertemuan ?? '-') }}</td>
                    </tr>
                    <tr class="py-2">
                        <td class="text-gray-500 pr-4 font-medium py-1.5">Ruangan / Mode</td>
                        <td class="text-gray-900 font-semibold py-1.5">: {{ $presensi->schedule->room->name ?? ($presensi->scheduleRequest->room->name ?? ($presensi->scheduleRequest->ruangan_usulan ?? '-')) }} / <span class="uppercase">{{ $presensi->status_kbm }}</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Honor Breakdown Table -->
        <div class="my-8">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Detail Presensi & Rincian Honor</h3>
            <div class="border border-gray-200 rounded-xl overflow-hidden print:border-black">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 font-bold text-gray-700 uppercase tracking-wider print:bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal Hadir</th>
                            <th class="px-4 py-3 text-center">Waktu KBM</th>
                            <th class="px-4 py-3 text-center">Durasi (Jam)</th>
                            <th class="px-4 py-3 text-right">Honor / Jam</th>
                            <th class="px-4 py-3 text-right">Total Honor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-4 py-4 font-semibold text-gray-900">
                                {{ $presensi->tanggal_hadir ? $presensi->tanggal_hadir->format('d F Y') : '-' }}
                            </td>
                            <td class="px-4 py-4 text-center font-medium text-gray-800">
                                {{ $presensi->jam_mulai }} – {{ $presensi->jam_selesai }}
                            </td>
                            <td class="px-4 py-4 text-center font-semibold text-gray-900">
                                {{ number_format($presensi->durasi_jam, 2) }} Jam
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-gray-800">
                                Rp {{ number_format($presensi->honor_per_jam, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right font-extrabold text-indigo-950">
                                Rp {{ number_format($presensi->honor_total, 0, ',', '.') }}
                            </td>
                        </tr>
                        @if($presensi->catatan)
                        <tr class="bg-gray-50/50">
                            <td colspan="5" class="px-4 py-3 text-xs text-gray-500 italic">
                                <strong>Catatan KBM:</strong> {{ $presensi->catatan }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="mt-16 grid grid-cols-2 gap-8 text-center text-sm">
            <div>
                <p class="text-gray-400 font-bold mb-16 uppercase tracking-wider text-xs">Dosen Pengampu,</p>
                <div class="w-48 mx-auto border-b border-gray-900 pt-2 print:border-black"></div>
                <p class="font-bold text-gray-900 mt-1">{{ $presensi->dosen->name ?? '-' }}</p>
                <p class="text-xs text-gray-400">NIDN: {{ $presensi->dosen->nidn ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-400 font-bold mb-16 uppercase tracking-wider text-xs">Dicatat Oleh BAA,</p>
                <div class="w-48 mx-auto border-b border-gray-900 pt-2 print:border-black"></div>
                <p class="font-bold text-gray-900 mt-1">{{ $presensi->dicatatOleh->name ?? 'Staf BAA' }}</p>
                <p class="text-xs text-gray-400">Dicatat: {{ $presensi->created_at ? $presensi->created_at->format('d M Y H:i') : '-' }}</p>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="mt-12 pt-6 border-t border-gray-100 text-center text-[10px] text-gray-400 uppercase tracking-widest print:border-black">
            Dokumen ini dicetak otomatis secara resmi oleh sistem pergantian jadwal lpkia.
        </div>
    </div>

    <!-- Auto Print Script -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Auto open print dialog
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
