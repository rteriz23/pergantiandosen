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
        $startTime = $request->get('start_time', '07:30');
        $endTime = $request->get('end_time', '09:40');
        
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
                
            $groupedRoomsStatus = [
                'r100' => [
                    'title' => 'Ruang Teori Lantai 1 (R. 100)',
                    'description' => 'Kelas reguler teori lantai 1',
                    'icon' => '🏛️',
                    'rooms' => []
                ],
                'r200' => [
                    'title' => 'Ruang Teori Lantai 2 (R. 200)',
                    'description' => 'Kelas reguler teori lantai 2 (Monitoring R. 200)',
                    'icon' => '🏢',
                    'rooms' => []
                ],
                'r300' => [
                    'title' => 'Ruang Teori Lantai 3 (R. 300)',
                    'description' => 'Kelas reguler teori lantai 3 (Monitoring R. 300)',
                    'icon' => '🏫',
                    'rooms' => []
                ],
                'labkom' => [
                    'title' => 'Laboratorium Komputer (Labkom / R. 201 & 300 Series Labs)',
                    'description' => 'Ruang praktikum komputer, perkantoran, basis data, aplikasi, pemrograman, multimedia',
                    'icon' => '💻',
                    'rooms' => []
                ],
                'other' => [
                    'title' => 'Fasilitas Lainnya & Online',
                    'description' => 'Aula utama dan kelas pembelajaran daring',
                    'icon' => '🌐',
                    'rooms' => []
                ],
            ];
                
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
                                'type' => 'Jadwal Reguler',
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
                                    'type' => 'Pengganti (' . $req->status . ')',
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
                
                $statusItem = [
                    'room' => $room,
                    'is_available' => !$isUsed,
                    'occupied_by' => $occupiedBy,
                ];
                
                $name = $room->name;
                if (stripos($name, 'lab') !== false || stripos($name, 'laboratorium') !== false) {
                    $groupedRoomsStatus['labkom']['rooms'][] = $statusItem;
                } elseif (preg_match('/Teori\s+1\d+/i', $name)) {
                    $groupedRoomsStatus['r100']['rooms'][] = $statusItem;
                } elseif (preg_match('/Teori\s+2\d+/i', $name)) {
                    $groupedRoomsStatus['r200']['rooms'][] = $statusItem;
                } elseif (preg_match('/Teori\s+3\d+/i', $name)) {
                    $groupedRoomsStatus['r300']['rooms'][] = $statusItem;
                } else {
                    $groupedRoomsStatus['other']['rooms'][] = $statusItem;
                }
            }

            // Calculate totals and statistics
            foreach ($groupedRoomsStatus as $key => $cat) {
                $total = count($cat['rooms']);
                $available = 0;
                foreach ($cat['rooms'] as $item) {
                    if ($item['is_available']) {
                        $available++;
                    }
                }
                $groupedRoomsStatus[$key]['total'] = $total;
                $groupedRoomsStatus[$key]['available'] = $available;
                $groupedRoomsStatus[$key]['occupied'] = $total - $available;
            }
            
            $roomsStatus = $groupedRoomsStatus;
        }
        
        $dosens = \App\Models\User::where('role', 'dosen')->orderBy('name')->get();
        return view('public.cari-jadwal', compact('date', 'startTime', 'endTime', 'roomsStatus', 'dosens'));
    }
}
