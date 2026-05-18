<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\User;
use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\Room;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $roomId = $request->get('room_id');
        $dosenId = $request->get('dosen_id');

        $query = Schedule::with(['dosen', 'room']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('mata_kuliah', 'like', "%{$search}%")
                  ->orWhere('kelas', 'like', "%{$search}%");
            });
        }

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        if ($dosenId) {
            $query->where('user_id', $dosenId);
        }

        $schedules = $query->orderBy('waktu_mulai', 'desc')->paginate(10)->withQueryString();
        
        $rooms = Room::all();
        $dosens = User::where('role', 'dosen')->get();

        return view('admin.schedule.index', compact('schedules', 'rooms', 'dosens', 'search', 'roomId', 'dosenId'));
    }

    public function create()
    {
        $dosens = User::where('role', 'dosen')->orderBy('name')->get();
        $matakuliahs = MataKuliah::where('is_active', true)->orderBy('nama')->get();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();
        
        $activePeriodes = \App\Models\Periode::where('is_active', true)->orderBy('name', 'desc')->pluck('name');

        return view('admin.schedule.create', compact('dosens', 'matakuliahs', 'kelas', 'rooms', 'activePeriodes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'matakuliah_id' => 'required|exists:mata_kuliahs,id',
            'kelas_id' => 'required|exists:kelas,id',
            'room_id' => 'nullable|exists:rooms,id',
            'tanggal' => 'required|date',
            'waktu_mulai_time' => 'required',
            'waktu_selesai_time' => 'required',
            'periode' => 'required|string',
            'pertemuan' => 'required|integer|min:1',
        ]);

        $tanggal = $request->tanggal;
        $start = Carbon::parse($tanggal . ' ' . $request->waktu_mulai_time);
        $end = Carbon::parse($tanggal . ' ' . $request->waktu_selesai_time);

        if ($end->lte($start)) {
            return back()->withInput()->withErrors(['waktu_selesai_time' => 'Waktu selesai harus setelah waktu mulai.']);
        }

        // Clash check for lecturer
        $clash = Schedule::where('user_id', $request->user_id)
            ->where('waktu_mulai', '<', $end)
            ->where('waktu_selesai', '>', $start)
            ->first();

        if ($clash) {
            return back()->withInput()->withErrors(['waktu_mulai_time' => "Dosen sudah memiliki jadwal lain pada jam tersebut: {$clash->mata_kuliah}."]);
        }

        // Clash check for room
        if ($request->room_id) {
            $roomClash = Schedule::where('room_id', $request->room_id)
                ->where('waktu_mulai', '<', $end)
                ->where('waktu_selesai', '>', $start)
                ->first();

            if ($roomClash) {
                return back()->withInput()->withErrors(['room_id' => "Ruangan sudah digunakan oleh jadwal lain pada jam tersebut: {$roomClash->mata_kuliah}."]);
            }
        }

        $dosen = User::findOrFail($request->user_id);
        $mk = MataKuliah::findOrFail($request->matakuliah_id);
        $kls = Kelas::findOrFail($request->kelas_id);

        $mataKuliahString = "{$mk->kode} - {$mk->nama} ({$mk->sks} SKS)";

        Schedule::create([
            'user_id' => $request->user_id,
            'prodi_id' => $dosen->prodi_id,
            'periode' => $request->periode,
            'mata_kuliah' => $mataKuliahString,
            'kelas' => $kls->nama_kelas,
            'pertemuan' => $request->pertemuan,
            'waktu_mulai' => $start,
            'waktu_selesai' => $end,
            'room_id' => $request->room_id,
            'status' => 'Terjadwal'
        ]);

        return redirect()->route('admin.schedule.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        
        // Extract dates and times
        $tanggal = Carbon::parse($schedule->waktu_mulai)->format('Y-m-d');
        $startTime = Carbon::parse($schedule->waktu_mulai)->format('H:i');
        $endTime = Carbon::parse($schedule->waktu_selesai)->format('H:i');

        $dosens = User::where('role', 'dosen')->orderBy('name')->get();
        $matakuliahs = MataKuliah::where('is_active', true)->orderBy('nama')->get();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();
        
        $activePeriodes = \App\Models\Periode::where('is_active', true)->orderBy('name', 'desc')->pluck('name');

        // Find matching Mata Kuliah and Kelas by string parsing
        $matchedMk = null;
        if (preg_match('/^([^\s]+)/', $schedule->mata_kuliah, $matches)) {
            $matchedMk = MataKuliah::where('kode', $matches[1])->first();
        }
        
        $matchedKls = Kelas::where('nama_kelas', $schedule->kelas)->first();

        return view('admin.schedule.edit', compact(
            'schedule', 'tanggal', 'startTime', 'endTime', 
            'dosens', 'matakuliahs', 'kelas', 'rooms', 'activePeriodes',
            'matchedMk', 'matchedKls'
        ));
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'matakuliah_id' => 'required|exists:mata_kuliahs,id',
            'kelas_id' => 'required|exists:kelas,id',
            'room_id' => 'nullable|exists:rooms,id',
            'tanggal' => 'required|date',
            'waktu_mulai_time' => 'required',
            'waktu_selesai_time' => 'required',
            'periode' => 'required|string',
            'pertemuan' => 'required|integer|min:1',
        ]);

        $tanggal = $request->tanggal;
        $start = Carbon::parse($tanggal . ' ' . $request->waktu_mulai_time);
        $end = Carbon::parse($tanggal . ' ' . $request->waktu_selesai_time);

        if ($end->lte($start)) {
            return back()->withInput()->withErrors(['waktu_selesai_time' => 'Waktu selesai harus setelah waktu mulai.']);
        }

        // Clash check for lecturer
        $clash = Schedule::where('user_id', $request->user_id)
            ->where('id', '!=', $id)
            ->where('waktu_mulai', '<', $end)
            ->where('waktu_selesai', '>', $start)
            ->first();

        if ($clash) {
            return back()->withInput()->withErrors(['waktu_mulai_time' => "Dosen sudah memiliki jadwal lain pada jam tersebut: {$clash->mata_kuliah}."]);
        }

        // Clash check for room
        if ($request->room_id) {
            $roomClash = Schedule::where('room_id', $request->room_id)
                ->where('id', '!=', $id)
                ->where('waktu_mulai', '<', $end)
                ->where('waktu_selesai', '>', $start)
                ->first();

            if ($roomClash) {
                return back()->withInput()->withErrors(['room_id' => "Ruangan sudah digunakan oleh jadwal lain pada jam tersebut: {$roomClash->mata_kuliah}."]);
            }
        }

        $dosen = User::findOrFail($request->user_id);
        $mk = MataKuliah::findOrFail($request->matakuliah_id);
        $kls = Kelas::findOrFail($request->kelas_id);

        $mataKuliahString = "{$mk->kode} - {$mk->nama} ({$mk->sks} SKS)";

        $schedule->update([
            'user_id' => $request->user_id,
            'prodi_id' => $dosen->prodi_id,
            'periode' => $request->periode,
            'mata_kuliah' => $mataKuliahString,
            'kelas' => $kls->nama_kelas,
            'pertemuan' => $request->pertemuan,
            'waktu_mulai' => $start,
            'waktu_selesai' => $end,
            'room_id' => $request->room_id,
        ]);

        return redirect()->route('admin.schedule.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('admin.schedule.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header
        $header = fgetcsv($handle, 1000, ';');
        // fallback to comma
        if (count($header) == 1 && strpos($header[0], ',') !== false) {
            rewind($handle);
            $header = fgetcsv($handle, 1000, ',');
            $delimiter = ',';
        } else {
            $delimiter = ';';
        }
        
        $importedCount = 0;
        $skippedCount = 0;
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            if (count($row) < 7 || empty($row[0])) continue;
            
            $dosenEmail = $row[0];
            $mkKode = $row[1];
            $kelasName = $row[2];
            $roomName = $row[3] ?? null;
            $tanggal = $row[4];
            $waktuMulai = $row[5];
            $waktuSelesai = $row[6];
            $periode = $row[7] ?? '2026 Ganjil';
            $pertemuan = intval($row[8] ?? 1);

            $dosen = User::where('email', $dosenEmail)->first();
            $mk = MataKuliah::where('kode', $mkKode)->first();
            
            if (!$dosen || !$mk) {
                $skippedCount++;
                continue;
            }

            $start = Carbon::parse($tanggal . ' ' . $waktuMulai);
            $end = Carbon::parse($tanggal . ' ' . $waktuSelesai);

            $room = null;
            if (!empty($roomName)) {
                $room = Room::firstOrCreate(['name' => $roomName], ['type' => 'Teori', 'capacity' => 40, 'is_active' => true]);
            }

            $mataKuliahString = "{$mk->kode} - {$mk->nama} ({$mk->sks} SKS)";

            Schedule::create([
                'user_id' => $dosen->id,
                'prodi_id' => $dosen->prodi_id,
                'periode' => $periode,
                'mata_kuliah' => $mataKuliahString,
                'kelas' => $kelasName,
                'pertemuan' => $pertemuan,
                'waktu_mulai' => $start,
                'waktu_selesai' => $end,
                'room_id' => $room ? $room->id : null,
                'status' => 'Terjadwal'
            ]);
            $importedCount++;
        }
        fclose($handle);

        return redirect()->route('admin.schedule.index')->with('success', "$importedCount data jadwal berhasil diimport. ($skippedCount dilewati karena Dosen / MK tidak ditemukan)");
    }
}
