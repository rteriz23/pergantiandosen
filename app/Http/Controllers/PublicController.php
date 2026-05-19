<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\ScheduleRequest;
use App\Models\KalenderAkademik;
use Carbon\Carbon;

class PublicController extends Controller
{
    public function jadwalRuangan(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $rooms = Room::all();
        
        // Cari request yang pending / disetujui (mem-booking ruangan)
        $approvedRequests = ScheduleRequest::whereIn('status', ['Pending', 'Disetujui'])
            ->whereNotNull('room_id')
            ->whereDate('waktu_mulai_usulan', $date)
            ->with(['schedule.prodi', 'pengaju', 'room'])
            ->get();

        // Cari jadwal reguler yang berlangsung pada tanggal tersebut
        $regularSchedules = Schedule::whereDate('waktu_mulai', $date)
            ->where('status', '!=', 'Diganti')
            ->with(['dosen', 'room'])
            ->orderBy('waktu_mulai')
            ->get();

        return view('public.jadwal-ruangan', compact('rooms', 'approvedRequests', 'regularSchedules', 'date'));
    }

    public function kalenderAkademik()
    {
        $kalenders = KalenderAkademik::orderBy('tanggal_mulai', 'asc')->get();
        return view('public.kalender-akademik', compact('kalenders'));
    }

    public function cariJadwalKosong(Request $request)
    {
        $date = $request->get('date');
        $startTime = $request->get('start_time', '07:00');
        $endTime = $request->get('end_time', '18:00');
        
        $roomsStatus = [];
        
        if ($date) {
            $rooms = Room::where('is_active', true)->orderBy('name')->get();
            
            // Selected slot start and end
            $slotStart = Carbon::parse($date . ' ' . $startTime);
            $slotEnd = Carbon::parse($date . ' ' . $endTime);
            
            // Get all regular schedules on this date
            $regularSchedules = Schedule::whereDate('waktu_mulai', $date)
                ->where('status', '!=', 'Diganti')
                ->with(['dosen'])
                ->get();
                
            // Get all pending/approved replacement requests on this date
            $replacementRequests = ScheduleRequest::whereDate('waktu_mulai_usulan', $date)
                ->whereIn('status', ['Pending', 'Disetujui'])
                ->with(['schedule.dosen'])
                ->get();
                
            foreach ($rooms as $room) {
                $isUsed = false;
                $occupiedBy = null;
                
                // 1. Check regular schedules
                foreach ($regularSchedules as $s) {
                    if ($s->room_id == $room->id) {
                        $schedStart = Carbon::parse($s->waktu_mulai);
                        $schedEnd = Carbon::parse($s->waktu_selesai);
                        
                        // Check overlap
                        if ($slotStart->lt($schedEnd) && $slotEnd->gt($schedStart)) {
                            $isUsed = true;
                            $occupiedBy = [
                                'type' => 'REGULER',
                                'dosen' => $s->dosen->name ?? 'Dosen',
                                'mata_kuliah' => $s->mata_kuliah,
                                'kelas' => $s->kelas,
                                'waktu' => $schedStart->format('H:i') . ' - ' . $schedEnd->format('H:i')
                            ];
                            break;
                        }
                    }
                }
                
                // 2. Check replacement requests (if not already found occupied)
                if (!$isUsed) {
                    foreach ($replacementRequests as $req) {
                        if ($req->room_id == $room->id || $req->ruangan_usulan == $room->name) {
                            $reqStart = Carbon::parse($req->waktu_mulai_usulan);
                            $reqEnd = Carbon::parse($req->waktu_selesai_usulan);
                            
                            // Check overlap
                            if ($slotStart->lt($reqEnd) && $slotEnd->gt($reqStart)) {
                                $isUsed = true;
                                $occupiedBy = [
                                    'type' => 'PENGGANTI',
                                    'dosen' => $req->schedule->dosen->name ?? $req->pengaju_nama ?? 'Dosen',
                                    'mata_kuliah' => $req->schedule->mata_kuliah,
                                    'kelas' => $req->schedule->kelas,
                                    'waktu' => $reqStart->format('H:i') . ' - ' . $reqEnd->format('H:i')
                                ];
                                break;
                            }
                        }
                    }
                }
                
                $roomsStatus[] = [
                    'room' => $room,
                    'is_available' => !$isUsed,
                    'occupied_by' => $occupiedBy,
                ];
            }
        }
        
        return view('public.cari-jadwal', compact('date', 'startTime', 'endTime', 'roomsStatus'));
    }
}
