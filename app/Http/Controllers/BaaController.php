<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduleRequest;
use App\Models\PresensiDosen;
use App\Models\Room;
use App\Models\SlaSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BaaController extends Controller
{
    public function requests(Request $request)
    {
        $requests = ScheduleRequest::with(['schedule.prodi', 'schedule.dosen', 'pengaju', 'room', 'presensi'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->has('read_notification')) {
            $notif = auth()->user()->notifications()->find($request->read_notification);
            if ($notif) { $notif->markAsRead(); }
        }

        $rooms = Room::where('is_active', true)->orderBy('name')->get();

        return view('baa.requests', compact('requests', 'rooms'));
    }

    public function recordPresensi(Request $request)
    {
        $request->validate([
            'schedule_request_id' => 'nullable|exists:schedule_requests,id',
            'schedule_id'         => 'nullable|exists:schedules,id',
            'dosen_id'            => 'required|exists:users,id',
            'tanggal_hadir'       => 'required|date',
            'jam_mulai'           => 'required',
            'jam_selesai'         => 'required',
            'status_kbm'          => 'required|in:hadir,online,izin,sakit',
            'catatan'             => 'nullable|string',
        ]);

        $dosen        = User::findOrFail($request->dosen_id);
        $honorPerJam  = $dosen->honor_per_jam ?? 0;

        $start    = Carbon::parse($request->jam_mulai);
        $end      = Carbon::parse($request->jam_selesai);
        $durasi   = round($end->diffInMinutes($start) / 60, 2);
        $honor    = round($durasi * $honorPerJam, 2);

        PresensiDosen::create([
            'schedule_request_id' => $request->schedule_request_id,
            'schedule_id'         => $request->schedule_id,
            'dosen_id'            => $request->dosen_id,
            'tanggal_hadir'       => $request->tanggal_hadir,
            'jam_mulai'           => $request->jam_mulai,
            'jam_selesai'         => $request->jam_selesai,
            'durasi_jam'          => $durasi,
            'honor_per_jam'       => $honorPerJam,
            'honor_total'         => $honor,
            'status_kbm'          => $request->status_kbm,
            'catatan'             => $request->catatan,
            'dicatat_oleh'        => Auth::id(),
        ]);

        return back()->with('success', 'Presensi dosen berhasil dicatat.');
    }

    public function exportHonor(Request $request)
    {
        $periode = $request->get('periode');
        $query   = PresensiDosen::with(['dosen', 'scheduleRequest.schedule'])
            ->orderBy('tanggal_hadir', 'desc');

        if ($periode) {
            $query->whereHas('scheduleRequest.schedule', function($q) use ($periode) { $q->where('periode', $periode); });
        }

        $records = $query->get();

        $csvHeader = ['Tanggal', 'Dosen', 'NIDN', 'Jam Mulai', 'Jam Selesai', 'Durasi (Jam)', 'Honor/Jam', 'Honor Total', 'Status KBM', 'Catatan'];
        $rows = $records->map(fn($r) => [
            $r->tanggal_hadir ? $r->tanggal_hadir->format('Y-m-d') : null,
            $r->dosen->name ?? '-',
            $r->dosen->nidn ?? '-',
            $r->jam_mulai,
            $r->jam_selesai,
            $r->durasi_jam,
            $r->honor_per_jam,
            $r->honor_total,
            $r->status_kbm,
            $r->catatan,
        ]);

        $filename = 'honor_presensi_dosen_' . ($periode ?? 'semua') . '_' . now()->format('Ymd') . '.csv';
        $handle   = fopen('php://output', 'w');
        ob_start();
        fputcsv($handle, $csvHeader, ';');
        foreach ($rows as $row) fputcsv($handle, $row, ';');
        fclose($handle);
        $csv = ob_get_clean();

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function importHonor(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $file   = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle); // skip header row
        $count  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 8) continue;
            [$tanggal, $dosenName, $nidn, $jamMulai, $jamSelesai, $durasi, $honorPerJam, $honorTotal, $statusKbm, $catatan] = array_pad($row, 10, null);

            $dosen = User::where('nidn', $nidn)->orWhere('name', $dosenName)->first();
            if (!$dosen) continue;

            PresensiDosen::create([
                'dosen_id'      => $dosen->id,
                'tanggal_hadir' => $tanggal,
                'jam_mulai'     => $jamMulai,
                'jam_selesai'   => $jamSelesai,
                'durasi_jam'    => (float)$durasi,
                'honor_per_jam' => (float)$honorPerJam,
                'honor_total'   => (float)$honorTotal,
                'status_kbm'    => $statusKbm ?? 'hadir',
                'catatan'       => $catatan,
                'dicatat_oleh'  => Auth::id(),
            ]);
            $count++;
        }

        fclose($handle);
        return back()->with('success', "{$count} record presensi berhasil diimpor.");
    }

    public function settings()
    {
        $sla    = SlaSetting::latest()->first();
        $rooms  = Room::orderBy('name')->get();
        $dosens = User::where('role', 'dosen')->orderBy('name')->get();

        return view('baa.settings', compact('sla', 'rooms', 'dosens'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate(['jam_sla' => 'required|integer|min:1|max:720']);

        SlaSetting::create([
            'jam_sla'    => $request->jam_sla,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Pengaturan SLA berhasil disimpan.');
    }

    public function toggleOnlineKBM(Request $request, $id)
    {
        $req = ScheduleRequest::findOrFail($id);
        $req->is_online = !$req->is_online;
        $req->catatan_baa = $request->catatan_baa ?? $req->catatan_baa;
        $req->save();

        return back()->with('success', 'Mode KBM berhasil diubah menjadi ' . ($req->is_online ? 'Online' : 'Offline') . '.');
    }

    public function assignRoom(Request $request, $id)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
        ]);

        $req = ScheduleRequest::findOrFail($id);
        $room = Room::findOrFail($request->room_id);
        
        $req->room_id = $room->id;
        $req->ruangan_usulan = $room->kode_ruangan ?? $room->name;
        $req->save();

        return back()->with('success', 'Ruangan berhasil di-assign untuk jadwal tersebut.');
    }

    public function rooms()
    {
        $rooms = Room::orderBy('name')->get();
        return view('baa.rooms', compact('rooms'));
    }

    public function storeRoom(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100|unique:rooms,name',
            'type'     => 'required|in:kelas,lab,aula,online',
            'capacity' => 'nullable|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        Room::create($request->only('name', 'type', 'capacity', 'keterangan') + ['is_active' => true]);
        return back()->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function updateRoom(Request $request, Room $room)
    {
        $request->validate([
            'name'      => 'required|string|max:100|unique:rooms,name,' . $room->id,
            'type'      => 'required|in:kelas,lab,aula,online',
            'capacity'  => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);
        $room->update($request->only('name', 'type', 'capacity', 'keterangan', 'is_active'));
        return back()->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroyRoom(Room $room)
    {
        $room->delete();
        return back()->with('success', 'Ruangan berhasil dihapus.');
    }

    public function updateDosenHonor(Request $request)
    {
        $request->validate([
            'dosen_id'     => 'required|exists:users,id',
            'honor_per_jam' => 'required|numeric|min:0',
            'nidn'          => 'nullable|string|max:20',
        ]);

        $dosen = User::findOrFail($request->dosen_id);
        $dosen->honor_per_jam = $request->honor_per_jam;
        if ($request->filled('nidn')) $dosen->nidn = $request->nidn;
        $dosen->save();

        return back()->with('success', 'Honor dosen berhasil diperbarui.');
    }

    // ── Rekap Presensi Mahasiswa ────────────────────────────────────────────

    /**
     * Halaman rekap presensi mahasiswa: daftar mahasiswa beserta
     * mata kuliah, dosen, jam, dan ruangan yang mereka ikuti.
     */
    public function rekapMahasiswa(Request $request)
    {
        $search  = $request->get('search');
        $periode = $request->get('periode');
        $prodiId = $request->get('prodi_id');

        $periodes = \App\Models\Periode::orderBy('name', 'desc')->pluck('name');
        $prodis   = \App\Models\Prodi::orderBy('name')->get();

        $query = \App\Models\Mahasiswa::with(['prodi'])->orderBy('nama');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nim',  'like', "%{$search}%")
                  ->orWhere('kelas','like', "%{$search}%");
            });
        }
        if ($prodiId) $query->where('prodi_id', $prodiId);

        $mahasiswas = $query->paginate(20)->withQueryString();

        // Untuk setiap mahasiswa, ambil jadwalnya (via pivot atau kelas)
        $mahasiswas->each(function ($mhs) use ($periode) {
            $mhs->jadwal_list = \App\Models\Schedule::where('kelas', $mhs->kelas)
                ->when($periode, fn($q) => $q->where('periode', $periode))
                ->with(['dosen', 'room'])
                ->orderBy('waktu_mulai')
                ->get()
                ->map(function ($s) {
                    return [
                        'id'           => $s->id,
                        'mata_kuliah'  => $s->mata_kuliah,
                        'dosen_nama'   => $s->dosen->name ?? '-',
                        'hari'         => \Carbon\Carbon::parse($s->waktu_mulai)->isoFormat('dddd'),
                        'jam'          => \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i')
                                        . ' - ' . \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i'),
                        'ruangan'      => $s->room->name ?? '-',
                        'ruangan_tipe' => $s->room->type ?? '-',
                        'pertemuan'    => $s->pertemuan,
                        'status'       => $s->status,
                        'periode'      => $s->periode,
                    ];
                });
        });

        return view('baa.rekap_mahasiswa', compact(
            'mahasiswas', 'periodes', 'prodis', 'search', 'periode', 'prodiId'
        ));
    }

    /**
     * Export rekap mahasiswa ke CSV.
     */
    public function exportRekapMahasiswa(Request $request)
    {
        $periode = $request->get('periode');
        $prodiId = $request->get('prodi_id');

        $mahasiswas = \App\Models\Mahasiswa::with('prodi')
            ->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId))
            ->orderBy('nama')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="rekap_mahasiswa_' . ($periode ?? 'semua') . '_' . now()->format('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($mahasiswas, $periode) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['NIM', 'Nama', 'Kelas', 'Program Studi', 'Status', 'Mata Kuliah', 'Dosen', 'Hari', 'Jam', 'Ruangan', 'Periode', 'Status Jadwal'], ';');

            foreach ($mahasiswas as $mhs) {
                $jadwals = \App\Models\Schedule::where('kelas', $mhs->kelas)
                    ->when($periode, fn($q) => $q->where('periode', $periode))
                    ->with(['dosen', 'room'])
                    ->orderBy('waktu_mulai')
                    ->get();

                if ($jadwals->isEmpty()) {
                    fputcsv($file, [
                        $mhs->nim, $mhs->nama, $mhs->kelas,
                        $mhs->prodi->name ?? '-',
                        $mhs->status_mengulang ? 'MENGULANG' : 'REGULER',
                        '-', '-', '-', '-', '-', '-', '-',
                    ], ';');
                } else {
                    foreach ($jadwals as $s) {
                        fputcsv($file, [
                            $mhs->nim,
                            $mhs->nama,
                            $mhs->kelas,
                            $mhs->prodi->name ?? '-',
                            $mhs->status_mengulang ? 'MENGULANG' : 'REGULER',
                            $s->mata_kuliah,
                            $s->dosen->name ?? '-',
                            \Carbon\Carbon::parse($s->waktu_mulai)->isoFormat('dddd'),
                            \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') . ' - ' . \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i'),
                            $s->room->name ?? '-',
                            $s->periode ?? '-',
                            $s->status,
                        ], ';');
                    }
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
