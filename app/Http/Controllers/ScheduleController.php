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
use App\Models\MahasiswaJadwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $query = Schedule::with('room')->where('user_id', $dosenId);
        if ($periode) $query->where('periode', $periode);

        $schedules = $query->get();
        $events = [];
        foreach ($schedules as $s) {
            $pendingReq = ScheduleRequest::where('schedule_id', $s->id)->where('status', 'Pending')->first();
            $color = '#3b82f6';
            if ($pendingReq)          $color = '#eab308';
            elseif ($s->status == 'Diganti') $color = '#22c55e';

            $roomName = $s->room ? $s->room->name : 'N/A';
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
                    'room'                => $roomName,
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
        $date      = $request->get('date');
        $startTime = $request->get('start_time', '07:30');
        $endTime   = $request->get('end_time', '09:40');
        $dosenId   = $request->get('dosen_id');
        $roomName  = $request->get('room');

        if (!$dosenId || !$date) {
            return response()->json(['error' => 'dosen_id and date are required'], 400);
        }

        $proposedStart = Carbon::parse($date . ' ' . $startTime);
        $proposedEnd   = Carbon::parse($date . ' ' . $endTime);
        $isSunday      = Carbon::parse($date)->isSunday();

        // 1. Check if the Dosen has an active regular class overlap at this time
        $dosenSchedules = Schedule::where('user_id', $dosenId)
            ->whereDate('waktu_mulai', $date)
            ->where('status', '!=', 'Diganti')
            ->get();
            
        $dosenScheduleConflict = false;
        foreach ($dosenSchedules as $s) {
            $sStart = Carbon::parse($s->waktu_mulai);
            $sEnd   = Carbon::parse($s->waktu_selesai);
            if ($proposedStart->lt($sEnd) && $proposedEnd->gt($sStart)) {
                $dosenScheduleConflict = true;
                break;
            }
        }

        // 2. Check if the Dosen has any approved/pending replacement requests overlap at this time
        $dosenRequests = ScheduleRequest::whereHas('schedule', function($q) use ($dosenId) { 
                $q->where('user_id', $dosenId); 
            })
            ->whereDate('waktu_mulai_usulan', $date)
            ->whereIn('status', ['Pending', 'Disetujui'])
            ->get();

        $dosenRequestConflict = false;
        foreach ($dosenRequests as $req) {
            $rStart = Carbon::parse($req->waktu_mulai_usulan);
            $rEnd   = Carbon::parse($req->waktu_selesai_usulan);
            if ($proposedStart->lt($rEnd) && $proposedEnd->gt($rStart)) {
                $dosenRequestConflict = true;
                break;
            }
        }

        // 3. Check if the selected Room is occupied (regular schedule or replacement request)
        $roomRecord = null;
        $roomConflict = null;
        if ($roomName) {
            $roomRecord = Room::where('name', $roomName)->first();
            
            // Check regular weekly schedules in this room
            $roomSchedules = Schedule::where(function($q) use ($roomRecord, $roomName) {
                    if ($roomRecord) {
                        $q->where('room_id', $roomRecord->id);
                    } else {
                        $q->where('ruangan_usulan', $roomName); // fallback
                    }
                })
                ->whereDate('waktu_mulai', $date)
                ->where('status', '!=', 'Diganti')
                ->with('dosen')
                ->get();

            foreach ($roomSchedules as $s) {
                $sStart = Carbon::parse($s->waktu_mulai);
                $sEnd   = Carbon::parse($s->waktu_selesai);
                if ($proposedStart->lt($sEnd) && $proposedEnd->gt($sStart)) {
                    $roomConflict = [
                        'type' => 'Jadwal Reguler',
                        'dosen' => $s->dosen->name ?? 'Dosen',
                        'mata_kuliah' => $s->mata_kuliah,
                        'kelas' => $s->kelas,
                        'waktu' => $sStart->format('H:i') . ' - ' . $sEnd->format('H:i')
                    ];
                    break;
                }
            }

            // Check replacement requests in this room
            if (!$roomConflict) {
                $roomRequests = ScheduleRequest::where(function($q) use ($roomRecord, $roomName) {
                        if ($roomRecord) {
                            $q->where('room_id', $roomRecord->id)->orWhere('ruangan_usulan', $roomName);
                        } else {
                            $q->where('ruangan_usulan', $roomName);
                        }
                    })
                    ->whereDate('waktu_mulai_usulan', $date)
                    ->whereIn('status', ['Pending', 'Disetujui'])
                    ->with('schedule.dosen')
                    ->get();

                foreach ($roomRequests as $req) {
                    $rStart = Carbon::parse($req->waktu_mulai_usulan);
                    $rEnd   = Carbon::parse($req->waktu_selesai_usulan);
                    if ($proposedStart->lt($rEnd) && $proposedEnd->gt($rStart)) {
                        $roomConflict = [
                            'type' => 'Pengganti (' . $req->status . ')',
                            'dosen' => $req->schedule->dosen->name ?? $req->pengaju_nama ?? 'Dosen',
                            'mata_kuliah' => $req->schedule->mata_kuliah,
                            'kelas' => $req->schedule->kelas,
                            'waktu' => $rStart->format('H:i') . ' - ' . $rEnd->format('H:i')
                        ];
                        break;
                    }
                }
            }
        }

        return response()->json([
            'is_sunday'                => $isSunday,
            'dosen_schedule_conflict'  => $dosenScheduleConflict,
            'dosen_request_conflict'   => $dosenRequestConflict,
            'room_conflict'            => $roomConflict,
            'room_details'             => $roomRecord ? [
                'name'     => $roomRecord->name,
                'type'     => $roomRecord->type,
                'capacity' => $roomRecord->capacity,
            ] : null,
        ]);
    }

    public function requestChange($id)
    {
        $schedule = Schedule::with(['dosen', 'room'])->findOrFail($id);
        $rooms    = Room::where('is_active', true)->orderBy('name')->get();
        return view('schedules.request', compact('schedule', 'rooms'));
    }

    public function requestGeneral(Request $request)
    {
        $dosenId = $request->get('dosen_id');
        $date    = $request->get('date');
        $prefilledRoom = $request->get('room');

        if (!$dosenId) {
            return redirect()->route('schedules.public')->with('error', 'Silakan pilih dosen terlebih dahulu.');
        }

        $dosen     = User::findOrFail($dosenId);
        $schedules = Schedule::with('room')->where('user_id', $dosenId)->where('status', 'Terjadwal')->get();
        $rooms     = Room::where('is_active', true)->orderBy('name')->get();

        $prefilledTime = null;
        if ($date) {
            try {
                $prefilledTime = Carbon::parse(str_replace(' ', '+', $date));
            } catch (\Exception $e) {
                $prefilledTime = null;
            }
        }

        return view('schedules.request_new', compact('dosen', 'schedules', 'prefilledTime', 'rooms', 'prefilledRoom'));
    }

    // ── API: Jadwal Mahasiswa by NIM ──────────────────────────────────────────

    /**
     * GET /api/mahasiswa/{nim}/jadwal
     * Kembalikan daftar jadwal (mata kuliah, dosen, jam, ruangan) milik mahasiswa.
     * Sumber data: mahasiswa_jadwal pivot ATAU fallback kelas-matching.
     */
    public function apiMahasiswaJadwal(Request $request, $nim)
    {
        $periode = $request->get('periode');

        $mahasiswa = Mahasiswa::where('nim', $nim)->with('prodi')->first();
        if (!$mahasiswa) {
            return response()->json(['error' => 'Mahasiswa tidak ditemukan.'], 404);
        }

        // Cari lewat pivot dulu
        $pivotQuery = Schedule::join('mahasiswa_jadwal', 'schedules.id', '=', 'mahasiswa_jadwal.schedule_id')
            ->where('mahasiswa_jadwal.mahasiswa_id', $mahasiswa->id)
            ->with(['dosen', 'room', 'prodi']);
        if ($periode) $pivotQuery->where('schedules.periode', $periode);
        $pivotSchedules = $pivotQuery->select('schedules.*', 'mahasiswa_jadwal.tipe_enrollment')
            ->orderBy('schedules.waktu_mulai')
            ->get();

        // Fallback: kelas-matching jika pivot kosong
        if ($pivotSchedules->isEmpty() && $mahasiswa->kelas) {
            $kelasQuery = Schedule::where('kelas', $mahasiswa->kelas)
                ->with(['dosen', 'room', 'prodi']);
            if ($periode) $kelasQuery->where('periode', $periode);
            $pivotSchedules = $kelasQuery->orderBy('waktu_mulai')->get()
                ->map(function($s) use ($mahasiswa) {
                    $s->tipe_enrollment = $mahasiswa->status_mengulang ? 'pengulang' : 'reguler';
                    return $s;
                });
        }

        $result = $pivotSchedules->map(function($s) {
            return [
                'id'              => $s->id,
                'mata_kuliah'     => $s->mata_kuliah,
                'kelas'           => $s->kelas,
                'pertemuan'       => $s->pertemuan,
                'dosen_id'        => $s->user_id,
                'dosen_nama'      => $s->dosen->name ?? '-',
                'waktu_mulai'     => $s->waktu_mulai,
                'waktu_selesai'   => $s->waktu_selesai,
                'hari'            => \Carbon\Carbon::parse($s->waktu_mulai)->isoFormat('dddd'),
                'jam'             => \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') . ' - ' . \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i'),
                'ruangan'         => $s->room->name ?? ($s->ruangan_usulan ?? '-'),
                'ruangan_tipe'    => $s->room->type ?? '-',
                'periode'         => $s->periode,
                'status'          => $s->status,
                'tipe_enrollment' => $s->tipe_enrollment ?? 'reguler',
                'prodi'           => $s->prodi->name ?? '-',
            ];
        });

        return response()->json([
            'mahasiswa' => [
                'nim'              => $mahasiswa->nim,
                'nama'             => $mahasiswa->nama,
                'email'            => $mahasiswa->email,
                'kelas'            => $mahasiswa->kelas,
                'status_mengulang' => $mahasiswa->status_mengulang,
                'prodi'            => $mahasiswa->prodi->name ?? '-',
            ],
            'jadwals'   => $result,
            'total'     => $result->count(),
        ]);
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

        $isPengulang = false;
        $isBentrokMahasiswa = false;
        $aiSolution = null;

        if ($request->pengaju_type === 'mahasiswa') {
            $nimNidn = $request->pengaju_nim_nidn;
            
            // Get or create Mahasiswa record to access their status
            $mahasiswa = Mahasiswa::where('nim', $nimNidn)->first();
            if (!$mahasiswa) {
                $mahasiswa = Mahasiswa::create([
                    'nim' => $nimNidn,
                    'nama' => $request->pengaju_nama ?? 'Mahasiswa Baru',
                    'email' => $request->pengaju_email,
                    'prodi_id' => $schedule->prodi_id,
                    'kelas' => $schedule->kelas,
                    'status_mengulang' => (stripos($schedule->kelas, 'pengulang') !== false || stripos($schedule->mata_kuliah, 'pengulang') !== false)
                ]);
            }
            
            $isPengulang = $mahasiswa->status_mengulang;

            // Check student SKS limit (< 6 SKS) for repeating student
            if ($isPengulang) {
                $existingRequests = ScheduleRequest::where('pengaju_nim_nidn', $nimNidn)
                    ->where('pengaju_type', 'mahasiswa')
                    ->whereIn('status', ['Pending', 'Disetujui'])
                    ->with('schedule')
                    ->get();
                
                $totalSks = 0;
                foreach ($existingRequests as $req) {
                    $sks = 3; // default
                    if (preg_match('/\((\d+)\s*SKS\)/i', $req->schedule->mata_kuliah, $matches)) {
                        $sks = (int)$matches[1];
                    }
                    $totalSks += $sks;
                }
                
                // Current request SKS
                $currentSks = 3;
                if (preg_match('/\((\d+)\s*SKS\)/i', $schedule->mata_kuliah, $matches)) {
                    $currentSks = (int)$matches[1];
                }
                
                if (($totalSks + $currentSks) >= 6) {
                    return back()->withErrors([
                        'pengaju_nim_nidn' => "Mahasiswa dengan status mengulang hanya diperbolehkan mengajukan pergantian kelas kurang dari 6 SKS. Total SKS yang diajukan sebelumnya: {$totalSks} SKS, kelas ini: {$currentSks} SKS. Total SKS: " . ($totalSks + $currentSks) . " SKS.",
                    ])->withInput();
                }
            }
        }

        $request->validate($rules);

        // Perform Clash Check
        $isBentrok = false;
        $bentrokDetails = [];

        // 1. Clash with lecturer schedules
        $clash = Schedule::where('user_id', $dosenId)
            ->where('id', '!=', $id)
            ->where('waktu_mulai', '<', $request->waktu_selesai_usulan)
            ->where('waktu_selesai', '>', $request->waktu_mulai_usulan)
            ->first();

        if ($clash) {
            $isBentrok = true;
            $bentrokDetails[] = "Jadwal Dosen mengajar yang lain (" . $clash->mata_kuliah . " - Kelas " . $clash->kelas . ")";
        }

        // 2. Room conflict (Schedule)
        $roomRecord = Room::where('name', $request->ruangan_usulan)->first();
        $roomClashSchedule = Schedule::where(function($q) use ($roomRecord, $request) {
                if ($roomRecord) {
                    $q->where('room_id', $roomRecord->id);
                } else {
                    $q->where('ruangan_usulan', $request->ruangan_usulan);
                }
            })
            ->whereDate('waktu_mulai', $request->waktu_mulai_usulan_date)
            ->where('status', '!=', 'Diganti')
            ->where('waktu_mulai', '<', $request->waktu_selesai_usulan)
            ->where('waktu_selesai', '>', $request->waktu_mulai_usulan)
            ->first();

        if ($roomClashSchedule) {
            $isBentrok = true;
            $bentrokDetails[] = "Ruangan digunakan jadwal reguler: " . $roomClashSchedule->mata_kuliah . " (" . $roomClashSchedule->kelas . ")";
        }

        // 3. Room conflict (Requests)
        $roomConflictReq = ScheduleRequest::where(function($q) use ($roomRecord, $request) {
                if ($roomRecord) {
                    $q->where('room_id', $roomRecord->id)->orWhere('ruangan_usulan', $request->ruangan_usulan);
                } else {
                    $q->where('ruangan_usulan', $request->ruangan_usulan);
                }
            })
            ->where('waktu_mulai_usulan', '<', $request->waktu_selesai_usulan)
            ->where('waktu_selesai_usulan', '>', $request->waktu_mulai_usulan)
            ->whereIn('status', ['Pending', 'Disetujui'])
            ->where('schedule_id', '!=', $id)
            ->first();

        if ($roomConflictReq) {
            $isBentrok = true;
            $bentrokDetails[] = "Ruangan digunakan pengajuan lain: " . $roomConflictReq->schedule->mata_kuliah . " (" . $roomConflictReq->schedule->kelas . ")";
        }

        // 4. Clash with Student Schedules (if student)
        if ($request->pengaju_type === 'mahasiswa' && isset($mahasiswa)) {
            // Check student's regular class schedules
            $studentRegularConflict = Schedule::where('kelas', $mahasiswa->kelas)
                ->where('status', '!=', 'Diganti')
                ->where('id', '!=', $schedule->id)
                ->where('waktu_mulai', '<', $request->waktu_selesai_usulan)
                ->where('waktu_selesai', '>', $request->waktu_mulai_usulan)
                ->first();

            if ($studentRegularConflict) {
                $isBentrok = true;
                $bentrokDetails[] = "Jadwal kuliah reguler kelas Anda (" . $studentRegularConflict->mata_kuliah . " - Kelas " . $studentRegularConflict->kelas . ")";
            }

            // Check student's other replacement requests
            $studentRequestConflict = ScheduleRequest::where('pengaju_nim_nidn', $nimNidn)
                ->where('pengaju_type', 'mahasiswa')
                ->whereIn('status', ['Pending', 'Disetujui'])
                ->where('schedule_id', '!=', $id)
                ->where('waktu_mulai_usulan', '<', $request->waktu_selesai_usulan)
                ->where('waktu_selesai_usulan', '>', $request->waktu_mulai_usulan)
                ->first();

            if ($studentRequestConflict) {
                $isBentrok = true;
                $bentrokDetails[] = "Jadwal pengajuan kelas Anda yang lain (" . $studentRequestConflict->schedule->mata_kuliah . ")";
            }
        }

        // Handle Conflict Resolution
        if ($isBentrok) {
            if ($request->pengaju_type === 'mahasiswa' && $isPengulang) {
                // Repeating student: ALLOW submission but generate AI Recommendation!
                $isBentrokMahasiswa = true;
                
                // Find alternative slot on same day
                $altSlot = $this->findAlternativeSlot($schedule, $mahasiswa, $request->waktu_mulai_usulan_date, $request->ruangan_usulan);
                
                $aiSolution = "\n\n🤖 [AI SOLUSI BENTROK - MAHASISWA MENGULANG]:\n";
                $aiSolution .= "- Terdeteksi bentrok dengan: " . implode(', ', $bentrokDetails) . ".\n";
                if ($altSlot) {
                    $aiSolution .= "- Solusi Terbaik AI: Silakan geser usulan jam ke " . $altSlot['label'] . " (" . $altSlot['jam_mulai'] . " - " . $altSlot['jam_selesai'] . ") pada hari yang sama karena slot tersebut sepenuhnya KOSONG bagi Anda, Dosen, dan Ruangan.";
                } else {
                    $aiSolution .= "- Solusi Terbaik AI: Tidak ditemukan sesi kosong pada hari ini. Disarankan koordinasi dengan Dosen Pengampu (" . $schedule->dosen->name . ") untuk kuliah daring (centang opsi Daring) agar fleksibel, atau ajukan pada hari lain.";
                }
            } else {
                // Reguler student or Lecturer: BLOCK and fail validation
                $errorMessage = "Jadwal usulan bentrok dengan:\n" . implode("\n", $bentrokDetails);
                return back()->withErrors(['waktu_mulai_usulan' => $errorMessage])->withInput();
            }
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

        $badgeNotes = [];
        if ($isPengulang) $badgeNotes[] = '[PENGULANG]';
        if ($isBentrokMahasiswa) $badgeNotes[] = '[BENTROK]';

        $finalAlasan = $request->alasan;
        if (!empty($badgeNotes)) {
            $finalAlasan = implode(' ', $badgeNotes) . ' - ' . $finalAlasan;
        }
        if ($aiSolution) {
            $finalAlasan .= $aiSolution;
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
            'alasan'              => $finalAlasan,
            'is_online'           => $request->boolean('is_online'),
            'status'              => 'Pending',
            'sla_deadline'        => $slaDeadline,
        ]);

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

    /**
     * Scan LPKIA default lecture sessions to find a fully vacant slot.
     */
    private function findAlternativeSlot($schedule, $mahasiswa, $date, $roomName)
    {
        $sessions = \DB::table('jam_kuliahs')->where('is_active', true)->orderBy('urutan')->get();
        $dosenId = $schedule->user_id;
        
        foreach ($sessions as $session) {
            $startStr = $date . ' ' . $session->jam_mulai;
            $endStr = $date . ' ' . $session->jam_selesai;
            $start = Carbon::parse($startStr);
            $end = Carbon::parse($endStr);
            
            // Check Dosen schedules
            $dosenScheduleConflict = Schedule::where('user_id', $dosenId)
                ->where('status', '!=', 'Diganti')
                ->where('waktu_mulai', '<', $end)
                ->where('waktu_selesai', '>', $start)
                ->exists();
                
            if ($dosenScheduleConflict) continue;
            
            // Check Dosen requests
            $dosenRequestConflict = ScheduleRequest::whereHas('schedule', function($q) use ($dosenId) {
                    $q->where('user_id', $dosenId);
                })
                ->whereDate('waktu_mulai_usulan', $date)
                ->whereIn('status', ['Pending', 'Disetujui'])
                ->where('waktu_mulai_usulan', '<', $end)
                ->where('waktu_selesai_usulan', '>', $start)
                ->exists();
                
            if ($dosenRequestConflict) continue;
            
            // Check student schedules (regular class)
            $studentScheduleConflict = false;
            if ($mahasiswa && $mahasiswa->kelas) {
                $studentScheduleConflict = Schedule::where('kelas', $mahasiswa->kelas)
                    ->where('status', '!=', 'Diganti')
                    ->where('waktu_mulai', '<', $end)
                    ->where('waktu_selesai', '>', $start)
                    ->exists();
            }
            
            if ($studentScheduleConflict) continue;
            
            // Check room conflict
            $roomConflict = false;
            if ($roomName) {
                $roomRecord = Room::where('name', $roomName)->first();
                
                $roomScheduleConflict = Schedule::where(function($q) use ($roomRecord, $roomName) {
                        if ($roomRecord) {
                            $q->where('room_id', $roomRecord->id);
                        } else {
                            $q->where('ruangan_usulan', $roomName);
                        }
                    })
                    ->whereDate('waktu_mulai', $date)
                    ->where('status', '!=', 'Diganti')
                    ->where('waktu_mulai', '<', $end)
                    ->where('waktu_selesai', '>', $start)
                    ->exists();
                    
                if ($roomScheduleConflict) {
                    continue;
                }
                
                $roomReqConflict = ScheduleRequest::where(function($q) use ($roomRecord, $roomName) {
                        if ($roomRecord) {
                            $q->where('room_id', $roomRecord->id)->orWhere('ruangan_usulan', $roomName);
                        } else {
                            $q->where('ruangan_usulan', $roomName);
                        }
                    })
                    ->whereDate('waktu_mulai_usulan', $date)
                    ->whereIn('status', ['Pending', 'Disetujui'])
                    ->where('waktu_mulai_usulan', '<', $end)
                    ->where('waktu_selesai_usulan', '>', $start)
                    ->exists();
                    
                if ($roomReqConflict) {
                    continue;
                }
            }
            
            // If we reach here, this session slot is 100% free for Dosen, Student, and Room!
            return [
                'label' => $session->label,
                'jam_mulai' => Carbon::parse($session->jam_mulai)->format('H:i'),
                'jam_selesai' => Carbon::parse($session->jam_selesai)->format('H:i'),
            ];
        }
        
        return null;
    }
}
