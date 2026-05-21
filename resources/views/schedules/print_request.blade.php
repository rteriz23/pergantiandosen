<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pengajuan Pergantian Jadwal - #{{ $request->id }}</title>
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
                Cetak Bukti
            </button>
            <button onclick="window.close()" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl transition-all">
                Tutup
            </button>
        </div>

        <!-- LPKIA Letterhead -->
        <div class="border-b-4 border-indigo-900 pb-6 mb-6 flex flex-col md:flex-row items-center justify-between gap-4 print-border">
            <div class="text-center md:text-left">
                <h1 class="text-2xl font-black text-indigo-950 tracking-tight">INSTITUT LPKIA</h1>
                <p class="text-xs text-gray-500 font-semibold mt-0.5 uppercase tracking-wider">Layanan Akademik & Penjadwalan Kuliah</p>
                <p class="text-[10px] text-gray-400 mt-1">Jl. Soekarno Hatta No. 456, Bandung • Telp: (022) 7564256 • www.lpkia.ac.id</p>
            </div>
            <div class="text-right hidden md:block">
                <span class="px-4 py-1.5 bg-indigo-50 border border-indigo-200 text-indigo-800 rounded-xl text-xs font-bold uppercase tracking-widest print:border-black">
                    FORM PERGANDIAN
                </span>
            </div>
        </div>

        <!-- Title -->
        <div class="text-center my-6">
            <h2 class="text-xl font-bold text-gray-900 tracking-wide uppercase">Bukti Permohonan Pergantian Jadwal Dosen</h2>
            <p class="text-xs text-gray-500 font-semibold mt-1">Nomor Pengajuan: REQ-SCH-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</p>
        </div>

        <!-- Meta Details Box -->
        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl mb-6 text-sm print:bg-white print:border print:border-black">
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase">Tanggal Pengajuan</p>
                <p class="font-semibold text-gray-900">{{ $request->created_at ? $request->created_at->format('d M Y H:i') : '-' }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400 font-bold uppercase">Status Akhir</p>
                <p class="font-extrabold text-indigo-950 uppercase tracking-widest text-base">{{ $request->status }}</p>
            </div>
        </div>

        <!-- Requester Info -->
        <div class="border border-gray-200 rounded-2xl p-6 mb-8 print:border-black">
            <h3 class="text-xs font-black text-indigo-900 uppercase tracking-widest mb-4">A. Informasi Pengaju & Pemohon</h3>
            <table class="w-full text-sm">
                <tr class="border-b border-gray-100 py-2">
                    <td class="text-gray-500 pr-4 font-semibold py-1.5 w-1/3">Nama Pengaju</td>
                    <td class="text-gray-900 font-bold py-1.5">: {{ $request->pengaju_display_name }}</td>
                </tr>
                <tr class="border-b border-gray-100 py-2">
                    <td class="text-gray-500 pr-4 font-semibold py-1.5">NIM / NIDN</td>
                    <td class="text-gray-900 font-semibold py-1.5">: {{ $request->pengaju_nim_nidn }} (<span class="uppercase text-xs font-bold">{{ $request->pengaju_type }}</span>)</td>
                </tr>
                @if($request->pengaju_email)
                <tr class="py-2">
                    <td class="text-gray-500 pr-4 font-semibold py-1.5">Email Pengaju</td>
                    <td class="text-gray-900 font-medium py-1.5">: {{ $request->pengaju_email }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Schedule Compare Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Original Schedule -->
            <div class="border border-gray-200 rounded-2xl p-6 print:border-black">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">B. Jadwal Kuliah Asli (Semula)</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Mata Kuliah</p>
                        <p class="font-bold text-gray-900">{{ $request->schedule->mata_kuliah ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Dosen Pengampu</p>
                        <p class="font-semibold text-gray-800">{{ $request->schedule->dosen->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Kelas / Pertemuan</p>
                        <p class="font-semibold text-gray-800">Kelas {{ $request->schedule->kelas ?? '-' }} • Pertemuan {{ $request->schedule->pertemuan ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Waktu & Ruangan Asli</p>
                        <p class="font-bold text-indigo-900">
                            {{ $request->schedule ? \Carbon\Carbon::parse($request->schedule->waktu_mulai)->format('l, d M Y') : '-' }}<br>
                            Jam {{ $request->schedule ? \Carbon\Carbon::parse($request->schedule->waktu_mulai)->format('H:i') . ' - ' . \Carbon\Carbon::parse($request->schedule->waktu_selesai)->format('H:i') : '-' }}<br>
                            Ruang: {{ $request->schedule->room->name ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Proposed Schedule -->
            <div class="border border-indigo-200 bg-indigo-50/20 rounded-2xl p-6 print:border-black print:bg-white">
                <h3 class="text-xs font-black text-indigo-900 uppercase tracking-widest mb-4">C. Jadwal Usulan Pengganti</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Mata Kuliah (Sama)</p>
                        <p class="font-bold text-gray-900">{{ $request->schedule->mata_kuliah ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Dosen Pengajar Usulan</p>
                        <p class="font-bold text-gray-900">
                            {{ $request->dosenPengganti->name ?? ($request->schedule->dosen->name ?? '-') }}
                            @if($request->dosen_pengganti_id)
                            <span class="text-xs bg-indigo-100 text-indigo-800 px-1.5 py-0.5 rounded ml-1 font-bold">DOSEN PENGGANTI</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Mode Pengajaran</p>
                        <p class="font-bold text-gray-800">{{ $request->is_online ? 'Kuliah Online' : 'Kuliah Tatap Muka (Offline)' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Waktu & Ruangan Usulan</p>
                        <p class="font-bold text-emerald-800">
                            {{ \Carbon\Carbon::parse($request->waktu_mulai_usulan)->format('l, d M Y') }}<br>
                            Jam {{ \Carbon\Carbon::parse($request->waktu_mulai_usulan)->format('H:i') }} – {{ \Carbon\Carbon::parse($request->waktu_selesai_usulan)->format('H:i') }}<br>
                            Ruang: {{ $request->room->name ?? ($request->ruangan_usulan ?? '-') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alasan & AI recommendations -->
        <div class="border border-gray-200 rounded-2xl p-6 mb-8 print:border-black">
            <h3 class="text-xs font-black text-indigo-900 uppercase tracking-widest mb-3">D. Alasan Pengajuan & Keterangan Tambahan</h3>
            <p class="text-sm text-gray-700 leading-relaxed font-medium whitespace-pre-line bg-gray-50 p-4 rounded-xl border border-gray-100 print:bg-white print:border-black">
                {!! str_replace(['[PENGULANG]', '[BENTROK]', '🤖 [AI SOLUSI BENTROK - MAHASISWA MENGULANG]:'], ['<span class="bg-red-100 text-red-700 font-bold px-1.5 py-0.5 rounded text-xs">[PENGULANG]</span>', '<span class="bg-orange-100 text-orange-700 font-bold px-1.5 py-0.5 rounded text-xs">[BENTROK]</span>', '<strong>🤖 [AI SOLUSI BENTROK - MAHASISWA MENGULANG]:</strong>'], e($request->alasan)) !!}
            </p>
            
            @if($request->catatan_kaprodi)
            <div class="mt-4">
                <p class="text-xs text-gray-400 font-bold uppercase mb-1">Catatan Kaprodi</p>
                <p class="text-sm text-red-800 bg-red-50 p-3 rounded-lg border border-red-100 print:bg-white print:border-black">{{ $request->catatan_kaprodi }}</p>
            </div>
            @endif

            @if($request->catatan_baa)
            <div class="mt-4">
                <p class="text-xs text-gray-400 font-bold uppercase mb-1">Catatan BAA</p>
                <p class="text-sm text-blue-800 bg-blue-50 p-3 rounded-lg border border-blue-100 print:bg-white print:border-black">{{ $request->catatan_baa }}</p>
            </div>
            @endif
        </div>

        <!-- Signatures Matrix -->
        <div class="mt-16 grid grid-cols-3 gap-6 text-center text-xs">
            <div>
                <p class="text-gray-400 font-bold mb-16 uppercase tracking-wider">Pemohon / Pengaju,</p>
                <div class="w-32 mx-auto border-b border-gray-900 pt-2 print:border-black"></div>
                <p class="font-bold text-gray-900 mt-1">{{ $request->pengaju_display_name }}</p>
                <p class="text-gray-400">ID: {{ $request->pengaju_nim_nidn }}</p>
            </div>
            <div>
                <p class="text-gray-400 font-bold mb-16 uppercase tracking-wider">Verifikasi Kaprodi,</p>
                @if($request->approved_at || ($request->status !== 'Pending' && $request->status !== 'Ditolak'))
                <p class="text-[10px] text-green-700 font-extrabold tracking-widest uppercase mb-1 bg-green-50 py-1 inline-block px-2 rounded border border-green-200 print:bg-white print:border-black">APPROVED</p>
                @endif
                <div class="w-32 mx-auto border-b border-gray-900 pt-2 print:border-black"></div>
                <p class="font-bold text-gray-900 mt-1">Ketua Program Studi</p>
                <p class="text-gray-400">Tgl: {{ $request->approved_at ? $request->approved_at->format('d M Y') : '-' }}</p>
            </div>
            <div>
                <p class="text-gray-400 font-bold mb-16 uppercase tracking-wider">Verifikasi BAA,</p>
                @if($request->status === 'Disetujui')
                <p class="text-[10px] text-green-700 font-extrabold tracking-widest uppercase mb-1 bg-green-50 py-1 inline-block px-2 rounded border border-green-200 print:bg-white print:border-black">VALIDATED</p>
                @endif
                <div class="w-32 mx-auto border-b border-gray-900 pt-2 print:border-black"></div>
                <p class="font-bold text-gray-900 mt-1">Petugas BAA</p>
                <p class="text-gray-400">SLA: {{ $request->sla_hours_left < 0 ? 'Breached' : 'On-Time' }}</p>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="mt-12 pt-6 border-t border-gray-100 text-center text-[10px] text-gray-400 uppercase tracking-widest print:border-black">
            Lembar bukti pengajuan ini sah dan dicetak secara elektronik oleh sistem jadwal lpkia.
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
