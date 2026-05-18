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
        
        // Cari request yang disetujui (mem-booking ruangan)
        $approvedRequests = ScheduleRequest::where('status', 'Disetujui')
            ->whereNotNull('room_id')
            ->whereDate('waktu_mulai_usulan', $date)
            ->with(['schedule.prodi', 'pengaju', 'room'])
            ->get();

        // Cari jadwal reguler yang berlangsung pada tanggal tersebut
        $regularSchedules = Schedule::whereDate('waktu_mulai', $date)
            ->where('status', '!=', 'Diganti')
            ->with(['dosen'])
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
        $startTime = $request->get('start_time');
        $endTime = $request->get('end_time');
        
        $availableSlots = [];
        
        if ($date) {
            $rooms = Room::all();
            $approvedRequests = ScheduleRequest::where('status', 'Disetujui')
                ->whereNotNull('room_id')
                ->whereDate('waktu_mulai_usulan', $date)
                ->get();
            
            if ($startTime && $endTime) {
                // Check specific time range
                $slotStart = Carbon::parse($date . ' ' . $startTime);
                $slotEnd = Carbon::parse($date . ' ' . $endTime);
                
                foreach ($rooms as $room) {
                    $isUsed = false;
                    foreach ($approvedRequests as $req) {
                        if ($req->room_id == $room->id) {
                            $reqStart = Carbon::parse($req->waktu_mulai_usulan);
                            $reqEnd = Carbon::parse($req->waktu_selesai_usulan);
                            
                            if ($slotStart->lt($reqEnd) && $slotEnd->gt($reqStart)) {
                                $isUsed = true;
                                break;
                            }
                        }
                    }
                    
                    if (!$isUsed) {
                        $availableSlots[] = [
                            'room' => $room,
                            'start' => $startTime,
                            'end' => $endTime,
                            'label' => "{$room->name} ({$startTime} - {$endTime})"
                        ];
                    }
                }
            } else {
                // Generate time slots 07:00 to 18:00
                $startHour = 7;
                $endHour = 18;
                
                foreach ($rooms as $room) {
                    for ($hour = $startHour; $hour < $endHour; $hour++) {
                        for ($minute = 0; $minute < 60; $minute += 60) { // 1 hour intervals
                            $timeStartString = sprintf('%02d:%02d:00', $hour, $minute);
                            $timeEndString = sprintf('%02d:%02d:00', $hour + 1, $minute);
                            
                            $slotStart = Carbon::parse($date . ' ' . $timeStartString);
                            $slotEnd = Carbon::parse($date . ' ' . $timeEndString);
                            
                            $isUsed = false;
                            foreach ($approvedRequests as $req) {
                                if ($req->room_id == $room->id) {
                                    $reqStart = Carbon::parse($req->waktu_mulai_usulan);
                                    $reqEnd = Carbon::parse($req->waktu_selesai_usulan);
                                    
                                    if ($slotStart->lt($reqEnd) && $slotEnd->gt($reqStart)) {
                                        $isUsed = true;
                                        break;
                                    }
                                }
                            }
                            
                            if (!$isUsed) {
                                $availableSlots[] = [
                                    'room' => $room,
                                    'start' => $timeStartString,
                                    'end' => $timeEndString,
                                    'label' => "{$room->name} ({$timeStartString} - {$timeEndString})"
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return view('public.cari-jadwal', compact('date', 'startTime', 'endTime', 'availableSlots'));
    }
}
