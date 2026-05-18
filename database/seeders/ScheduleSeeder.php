<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Schedule;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Membaca data Dosen dan Jadwal dari file JSON seeder...');

        // Seed Dosen
        $dosenJson = File::get(database_path('seeders/data/dosen.json'));
        $dosenData = json_decode($dosenJson, true);
        
        foreach ($dosenData as $data) {
            $prodiName = $data['prodi_name'] ?? 'Umum';
            $prodi = \App\Models\Prodi::firstOrCreate(['name' => $prodiName]);
            $data['prodi_id'] = $prodi->id;
            
            unset($data['prodi_name']);
            if (array_key_exists('prodi', $data)) {
                unset($data['prodi']);
            }
            
            User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
        $this->command->info(count($dosenData) . ' Dosen berhasil di-seed.');

        // Seed Schedules
        $scheduleJson = File::get(database_path('seeders/data/schedules.json'));
        $scheduleData = json_decode($scheduleJson, true);
        
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Schedule::truncate(); // Hapus jadwal lama agar tidak duplikat
        \App\Models\Room::truncate(); // Hapus ruangan lama agar bersih
        \App\Models\MataKuliah::truncate(); // Hapus matakuliah lama agar bersih
        \App\Models\Kelas::truncate(); // Hapus kelas lama agar bersih
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // Buat 36 ruangan master
        $roomNames = [];
        for ($i = 101; $i <= 110; $i++) $roomNames[] = "R. $i";
        for ($i = 201; $i <= 210; $i++) $roomNames[] = "R. $i";
        for ($i = 301; $i <= 310; $i++) $roomNames[] = "R. $i";
        for ($i = 1; $i <= 5; $i++) $roomNames[] = "Lab. Komputer $i";
        $roomNames[] = "Aula";

        $rooms = [];
        foreach ($roomNames as $name) {
            $rooms[] = \App\Models\Room::create([
                'name' => $name,
                'type' => str_contains($name, 'Lab') ? 'lab' : (str_contains($name, 'Aula') ? 'aula' : 'kelas'),
                'capacity' => 40,
                'is_active' => true
            ]);
        }
        
        $count = 0;
        foreach ($scheduleData as $data) {
            $dosen = User::where('email', $data['dosen_email'])->first();
            $prodiName = $data['prodi_name'] ?? 'Umum';
            $prodi = \App\Models\Prodi::firstOrCreate(['name' => $prodiName]);
            
            if ($dosen) {
                // Populate Kelas
                if (!empty($data['kelas'])) {
                    \App\Models\Kelas::firstOrCreate([
                        'nama_kelas' => $data['kelas']
                    ]);
                }

                // Populate Mata Kuliah
                if (preg_match('/^([^\s]+)\s*-\s*(.+?)\s*\((\d+)\s*SKS\)$/', $data['mata_kuliah'], $matches)) {
                    $kode = $matches[1];
                    $nama = $matches[2];
                    $sks = intval($matches[3]);
                    
                    \App\Models\MataKuliah::updateOrCreate(
                        ['kode' => $kode],
                        [
                            'nama' => $nama,
                            'sks' => $sks,
                            'prodi_id' => $prodi->id,
                            'is_active' => true
                        ]
                    );
                } else {
                    // Fallback jika format tidak cocok
                    \App\Models\MataKuliah::updateOrCreate(
                        ['kode' => substr($data['mata_kuliah'], 0, 8)],
                        [
                            'nama' => $data['mata_kuliah'],
                            'sks' => 3,
                            'prodi_id' => $prodi->id,
                            'is_active' => true
                        ]
                    );
                }

                $start = $data['waktu_mulai'];
                $end = $data['waktu_selesai'];
                
                // Cari ruangan yang kosong di jam ini
                $assignedRoomId = null;
                foreach ($rooms as $r) {
                    $overlap = Schedule::where('room_id', $r->id)
                        ->where(function($q) use ($start, $end) {
                            $q->whereBetween('waktu_mulai', [$start, $end])
                              ->orWhereBetween('waktu_selesai', [$start, $end])
                              ->orWhere(function($q2) use ($start, $end) {
                                  $q2->where('waktu_mulai', '<=', $start)
                                     ->where('waktu_selesai', '>=', $end);
                              });
                        })->first();
                        
                    if (!$overlap) {
                        $assignedRoomId = $r->id;
                        break;
                    }
                }

                Schedule::create([
                    'user_id' => $dosen->id,
                    'prodi_id' => $prodi->id,
                    'periode' => $data['periode'],
                    'mata_kuliah' => $data['mata_kuliah'],
                    'kelas' => $data['kelas'],
                    'pertemuan' => $data['pertemuan'],
                    'waktu_mulai' => $start,
                    'waktu_selesai' => $end,
                    'status' => $data['status'],
                    'room_id' => $assignedRoomId,
                ]);
                $count++;
            }
        }
        
        $this->command->info($count . ' Jadwal berhasil di-seed dengan alokasi ruangan otomatis serta sync Mata Kuliah dan Kelas.');
    }
}
