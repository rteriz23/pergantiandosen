<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduleRequest;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Room;
use App\Models\SlaSetting;
use App\Models\KemahasiswaanSetting;
use App\Models\Mahasiswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function publicIndex(Request $request)
    {
        $dosens = User::where('role', 'dosen')
            ->with(['prodi', 'schedules' => function ($q) {
                $q->select('user_id', 'prodi_id')->distinct();
            }])
            ->orderBy('name')
            ->get();

        $dosens->each(function ($d) {
            $d->taught_prodi_ids = $d->schedules->pluck('prodi_id')
                ->push($d->prodi_id)
                ->filter()
                ->map(fn($id) => (int)$id)
                ->unique()
                ->values();
        });

        $prodis       = \App\Models\Prodi::orderBy('name')->get();
        $periodes     = \App\Models\Periode::where('is_active', true)->orderBy('name', 'desc')->pluck('name');
        $selectedDosenId = $request->get('dosen_id');
        $selectedPeriode = $request->get('periode');

        $historyRequests = collect();
        if ($selectedDosenId) {
            $historyRequests = ScheduleRequest::whereHas('schedule', function ($q) use ($selectedDosenId) {
                $q->where('user_id', $selectedDosenId);
            })->with(['schedule', 'room'])->orderBy('created_at', 'desc')->get();
        }

        $successRequest = null;
        if (session('success_request_id')) {
            $successRequest = ScheduleRequest::with(['schedule.dosen', 'pengaju', 'room'])
                ->find(session('success_request_id'));
        }

        return view('schedules.public', compact(
            'dosens', 'prodis', 'periodes', 'selectedDosenId', 'selectedPeriode',
            'historyRequests', 'successRequest'
        ));
    }

    // ── API ──────────────────────────────────────────────────────────────────

    public function apiSchedules(Request $request)
    {
        $dosenId = $request->get('dosen_id');
        $periode = $request->get('periode');

        if (!$dosenId) return response()->json([]);

        $query = Schedule::where('user_id', $dosenId);
        if ($periode) $query->where('periode', $periode);

        $schedules = $query->get();
        $events = [];
        foreach ($schedules as $s) {
            $pendingReq = ScheduleRequest::where('schedule_id', $s->id)->where('status', 'Pending')->first();
            $color = '#3b82f6';
            if ($pendingReq)          $color = '#eab308';
            elseif ($s->status == 'Diganti') $color = '#22c55e';

            $events[] = [
                'id'    => $s->id,
                'title' => $s->mata_kuliah . ' (' . $s->kelas . ')',
                'start' => $s->waktu_mulai,
                'end'   => $s->waktu_selesai,
                'color' => $color,
                'extendedProps' => [
                    'status'              => $s->status,
                    'pertemuan'           => $s->pertemuan,
                    'has_pending_request' => $pendingReq ? true : false,
                ],
            ];
        }
        return response()->json($events);
    }

    public function apiRooms(Request $request)
    {
        $rooms = Room::where('is_active', true)->orderBy('name')->get(['id', 'name', 'type', 'capacity']);
        return response()->json($rooms);
    }

    public function checkAvailability(Request $request)
    {
        $date    = Carbon::parse($request->get('date'));
        $dosenId = $request->get('dosen_id');
        $room    = $request->get('room');

        if (!$dosenId) return response()->json(['error' => 'dosen_id required'], 400);

        $isSunday = $date->isSunday();

        $dosenSchedules = Schedule::where('user_id', $dosenId)
            ->whereDate('waktu_mulai', $date)->get();

        // Also check pending/approved replacement requests on that date
        $dosenRequests = ScheduleRequest::whereHas('schedule', function($q) use ($dosenId) { $q->where('user_id', $dosenId); })
            ->whereDate('waktu_mulai_usulan', $date)
            ->whereIn('status', ['Pending', 'Disetujui'])
            ->get();

        $roomConflicts = [];
        if ($room) {
            $roomConflicts = ScheduleRequest::where('ruangan_usulan', $room)
                ->whereDate('waktu_mulai_usulan', $date)
                ->whereIn('status', ['Pending', 'Disetujui'])
                ->with('schedule.dosen')
                ->get();

            // Also check from rooms table
            $roomRecord = Room::where('name', $room)->first();
            if ($roomRecord) {
                $fromRoomTable = ScheduleRequest::where('room_id', $roomRecord->id)
                    ->whereDate('waktu_mulai_usulan', $date)
                    ->whereIn('status', ['Pending', 'Disetujui'])
                    ->with('schedule.dosen')
                    ->get();
                $roomConflicts = collect($roomConflicts)->merge($fromRoomTable)->unique('id')->values();
            }
        }

        return response()->json([
            'is_sunday'      => $isSunday,
            'schedules'      => $dosenSchedules,
            'dosen_requests' => $dosenRequests,
            'room_conflicts' => $roomConflicts,
        ]);
    }

    // ── Views ────────────────────────────────────────────────────────────────

    public function requestChange($id)
    {
        $schedule = Schedule::with('dosen')->findOrFail($id);
        $rooms    = Room::where('is_active', true)->orderBy('name')->get();
        return view('schedules.request', compact('schedule', 'rooms'));
    }

    public function requestGeneral(Request $request)
    {
        $dosenId = $request->get('dosen_id');
        $date    = $request->get('date');

        if (!$dosenId) {
            return redirect()->route('schedules.public')->with('error', 'Silakan pilih dosen terlebih dahulu.');
        }

        $dosen     = User::findOrFail($dosenId);
        $schedules = Schedule::where('user_id', $dosenId)->where('status', 'Terjadwal')->get();
        $rooms     = Room::where('is_active', true)->orderBy('name')->get();

        $prefilledTime = null;
        if ($date) {
            try {
                $prefilledTime = Carbon::parse(str_replace(' ', '+', $date));
            } catch (\Exception $e) {
                $prefilledTime = null;
            }
        }

        return view('schedules.request_new', compact('dosen', 'schedules', 'prefilledTime', 'rooms'));
    }

    // ── Store (supports anonymous) ────────────────────────────────────────────

    public function storeRequest(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $dosenId  = $schedule->user_id;

        // Prevent duplicate pending requests
        $existing = ScheduleRequest::where('schedule_id', $id)->where('status', 'Pending')->first();
        if ($existing) {
            return back()->withErrors(['waktu_mulai_usulan' => 'Jadwal ini sudah memiliki permohonan penggantian yang sedang diproses.']);
        }

        // Build datetime fields
        $startStr = $request->waktu_mulai_usulan_date . ' ' . $request->waktu_mulai_usulan_time;
        $endStr   = $request->waktu_mulai_usulan_date . ' ' . $request->waktu_selesai_usulan_time;
        $request->merge([
            'waktu_mulai_usulan'   => Carbon::parse($startStr)->toDateTimeString(),
            'waktu_selesai_usulan' => Carbon::parse($endStr)->toDateTimeString(),
        ]);

        // Validation rules — anonymous submissions also validated
        $rules = [
            'waktu_mulai_usulan'   => 'required|date',
            'waktu_selesai_usulan' => 'required|date|after:waktu_mulai_usulan',
            'ruangan_usulan'       => 'required|string|max:255',
            'alasan'               => 'required|string',
            'pengaju_type'         => 'required|in:dosen,mahasiswa',
        ];

        if (!Auth::check()) {
            // Anonymous: require identifier fields
            $rules['pengaju_nama']      = 'required|string|max:255';
            $rules['pengaju_nim_nidn']  = 'required|string|max:50';
            $rules['pengaju_email']     = 'nullable|email|max:255';
        }

        if ($request->pengaju_type === 'mahasiswa') {
            // Check student submission limit
            $prodiId  = $schedule->prodi_id;
            $setting  = KemahasiswaanSetting::getFor($prodiId);
            $nimNidn  = $request->pengaju_nim_nidn ?? ($request->pengaju_nim_nidn);
            $used     = ScheduleRequest::where('pengaju_nim_nidn', $nimNidn)
                ->where('pengaju_type', 'mahasiswa')
                ->whereHas('schedule', function($q) use ($prodiId) { $q->where('prodi_id', $prodiId); })
                ->count();

            if ($used >= $setting->max_pergantian) {
                return back()->withErrors([
                    'pengaju_nim_nidn' => "Batas pengajuan pergantian untuk mahasiswa ini sudah tercapai ({$setting->max_pergantian}x).",
                ]);
            }
        }

        $request->validate($rules);

        // Clash detection
        $clash = Schedule::where('user_id', $dosenId)
            ->where('id', '!=', $id)
            ->where('waktu_mulai', '<', $request->waktu_selesai_usulan)
            ->where('waktu_selesai', '>', $request->waktu_mulai_usulan)
            ->first();

        if ($clash) {
            return back()->withErrors(['waktu_mulai_usulan' => 'Jadwal usulan bentrok dengan jadwal Dosen ini yang lain (' . $clash->mata_kuliah . ').']);
        }

        // Room conflict check
        $roomConflict = ScheduleRequest::where('ruangan_usulan', $request->ruangan_usulan)
            ->where('waktu_mulai_usulan', '<', $request->waktu_selesai_usulan)
            ->where('waktu_selesai_usulan', '>', $request->waktu_mulai_usulan)
            ->whereIn('status', ['Pending', 'Disetujui'])
            ->where('schedule_id', '!=', $id)
            ->first();

        if ($roomConflict) {
            return back()->withErrors(['ruangan_usulan' => 'Ruangan ini sudah digunakan oleh pengajuan lain pada waktu yang sama.']);
        }

        // Determine SLA deadline
        $slaHours   = SlaSetting::getCurrent();
        $slaDeadline = now()->addHours($slaHours);

        // Resolve room_id from rooms table if exists
        $roomRecord = Room::where('name', $request->ruangan_usulan)->first();

        // Determine pengaju
        $pengajuId = null;
        if (Auth::check()) {
            $pengajuId = Auth::id();
        }

        $scheduleRequest = ScheduleRequest::create([
            'schedule_id'         => $schedule->id,
            'pengaju_id'          => $pengajuId,
            'pengaju_nama'        => $request->pengaju_nama ?? ($pengajuId ? null : $request->pengaju_nama),
            'pengaju_nim_nidn'    => $request->pengaju_nim_nidn,
            'pengaju_type'        => $request->pengaju_type ?? 'dosen',
            'pengaju_email'       => $request->pengaju_email,
            'waktu_mulai_usulan'  => $request->waktu_mulai_usulan,
            'waktu_selesai_usulan' => $request->waktu_selesai_usulan,
            'ruangan_usulan'      => $request->ruangan_usulan,
            'room_id'             => $roomRecord ? $roomRecord->id : null,
            'alasan'              => $request->alasan,
            'is_online'           => $request->boolean('is_online'),
            'status'              => 'Pending',
            'sla_deadline'        => $slaDeadline,
        ]);

        // Auto-register mahasiswa if not exists
        if ($request->pengaju_type === 'mahasiswa' && $request->pengaju_nim_nidn) {
            Mahasiswa::firstOrCreate(
                ['nim' => $request->pengaju_nim_nidn],
                [
                    'nama'     => $request->pengaju_nama ?? 'Unknown',
                    'email'    => $request->pengaju_email,
                    'prodi_id' => $schedule->prodi_id,
                ]
            );
        }

        // Notify Kaprodi
        $kaprodi = User::where('role', 'kaprodi')->where('prodi_id', $schedule->prodi_id)->first();
        if ($kaprodi) {
            $kaprodi->notify(new \App\Notifications\ScheduleRequestedNotification($scheduleRequest));
        }

        // Notify BAA
        $baas = User::where('role', 'baa')->get();
        foreach ($baas as $baa) {
            $baa->notify(new \App\Notifications\ScheduleRequestedNotification($scheduleRequest));
        }

        return redirect()->route('schedules.public', ['dosen_id' => $dosenId, 'periode' => $schedule->periode])
            ->with('success', 'Permohonan pergantian jadwal berhasil diajukan.')
            ->with('success_request_id', $scheduleRequest->id);
    }
}
