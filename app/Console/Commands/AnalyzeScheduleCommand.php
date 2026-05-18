<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AnalyzeScheduleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:analyze';
    protected $description = 'Analyze schedules, check for conflicts, and auto-assign rooms.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Menganalisa Jadwal Periode 2025 Genap...');
        
        $schedules = \App\Models\Schedule::where('periode', 'like', '%2025 Genap%')->orderBy('waktu_mulai')->get();
        $this->info('Ditemukan ' . $schedules->count() . ' jadwal pada periode ini.');
        
        // Buat beberapa ruangan master dummy jika belum ada
        $roomNames = ['R. 201', 'R. 202', 'R. 203', 'R. 204', 'Lab. Komputer 1', 'Lab. Komputer 2', 'Aula'];
        foreach ($roomNames as $name) {
            \App\Models\Room::firstOrCreate(
                ['name' => $name],
                ['type' => str_contains($name, 'Lab') ? 'lab' : (str_contains($name, 'Aula') ? 'aula' : 'kelas'), 'capacity' => 40, 'is_active' => true]
            );
        }
        $rooms = \App\Models\Room::all();
        
        $conflicts = [];
        $assignedCount = 0;
        
        // Group by dosen untuk cek bentrok dosen
        $byDosen = $schedules->groupBy('user_id');
        foreach ($byDosen as $dosenId => $dosenSchedules) {
            $dosen = \App\Models\User::find($dosenId);
            $sorted = $dosenSchedules->sortBy('waktu_mulai')->values();
            
            for ($i = 0; $i < $sorted->count() - 1; $i++) {
                if ($sorted[$i]->waktu_selesai > $sorted[$i+1]->waktu_mulai) {
                    $conflicts[] = [
                        'type' => 'dosen_bentrok',
                        'dosen' => $dosen->name,
                        's1' => $sorted[$i]->mata_kuliah . ' (' . $sorted[$i]->waktu_mulai . ')',
                        's2' => $sorted[$i+1]->mata_kuliah . ' (' . $sorted[$i+1]->waktu_mulai . ')'
                    ];
                }
            }
        }
        
        // Assign Rooms
        foreach ($schedules as $s) {
            if (!$s->room_id) {
                // Find an available room
                foreach ($rooms as $r) {
                    $overlap = \App\Models\Schedule::where('room_id', $r->id)
                        ->where('id', '!=', $s->id)
                        ->where(function($q) use ($s) {
                            $q->whereBetween('waktu_mulai', [$s->waktu_mulai, $s->waktu_selesai])
                              ->orWhereBetween('waktu_selesai', [$s->waktu_mulai, $s->waktu_selesai])
                              ->orWhere(function($q2) use ($s) {
                                  $q2->where('waktu_mulai', '<=', $s->waktu_mulai)
                                     ->where('waktu_selesai', '>=', $s->waktu_selesai);
                              });
                        })->first();
                        
                    if (!$overlap) {
                        $s->room_id = $r->id;
                        $s->save();
                        $assignedCount++;
                        break;
                    }
                }
            }
        }
        
        $this->info("Berhasil melakukan auto-assign ke {$assignedCount} jadwal yang belum memiliki ruangan.");
        if (count($conflicts) > 0) {
            $this->error("Ditemukan " . count($conflicts) . " jadwal yang bentrok!");
            foreach ($conflicts as $c) {
                $this->line("- Dosen: {$c['dosen']} | Jadwal 1: {$c['s1']} | Jadwal 2: {$c['s2']}");
            }
        } else {
            $this->info("Tidak ada jadwal yang bentrok. Jadwal valid!");
        }
        
        return 0;
    }
}
