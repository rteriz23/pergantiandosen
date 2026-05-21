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

        // Buat daftar ruangan master yang komprehensif (sesuai PDF + opsional kosong)
        $roomNames = [
            // Teori 100
            'Ruang Teori 101', 'Ruang Teori 102', 'Ruang Teori 103', 'Ruang Teori 104', 'Ruang Teori 105',
            'Ruang Teori 106', 'Ruang Teori 107', 'Ruang Teori 108', 'Ruang Teori 109', 'Ruang Teori 110',
            'Ruang Teori 111', 'Ruang Teori 112', 'Ruang Teori 113', 'Ruang Teori 140',
            // Teori 200
            'Ruang Teori 201', 'Ruang Teori 202', 'Ruang Teori 203', 'Ruang Teori 204', 'Ruang Teori 205',
            'Ruang Teori 206', 'Ruang Teori 207', 'Ruang Teori 208', 'Ruang Teori 209', 'Ruang Teori 210',
            // Teori 300
            'Ruang Teori 301', 'Ruang Teori 302', 'Ruang Teori 303', 'Ruang Teori 304', 'Ruang Teori 305',
            'Ruang Teori 306', 'Ruang Teori 307', 'Ruang Teori 308', 'Ruang Teori 309', 'Ruang Teori 310',
            // Labs / Labkom
            'Lab Perkantoran (R. 201)',
            'Lab. Multimedia 1 (R.301)',
            'Lab. Pemrograman (R. 302)',
            'Lab. Aplikasi (R.304)',
            'Lab. Multimedia 2 (R.305)',
            'Lab. Basis Data',
            'Aula',
            'Online'
        ];

        $rooms = [];
        foreach ($roomNames as $name) {
            $type = 'kelas';
            if (str_contains(strtolower($name), 'online')) {
                $type = 'online';
            } elseif (str_contains(strtolower($name), 'lab')) {
                $type = 'lab';
            } elseif (str_contains(strtolower($name), 'aula')) {
                $type = 'aula';
            }
            
            $rooms[$name] = \App\Models\Room::create([
                'name' => $name,
                'type' => $type,
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
                
                // Ambil dan normalisasi nama ruangan dari JSON
                $ruangName = $data['ruang'] ?? 'Ruang Teori 102';
                if ($ruangName === 'Lab Pemrograman 302') {
                    $ruangName = 'Lab. Pemrograman (R. 302)';
                } elseif ($ruangName === 'Lab.Basis Data') {
                    $ruangName = 'Lab. Basis Data';
                }

                // Cari atau buat ruangan jika belum terdaftar
                if (!isset($rooms[$ruangName])) {
                    $type = 'kelas';
                    if (str_contains(strtolower($ruangName), 'online')) {
                        $type = 'online';
                    } elseif (str_contains(strtolower($ruangName), 'lab')) {
                        $type = 'lab';
                    } elseif (str_contains(strtolower($ruangName), 'aula')) {
                        $type = 'aula';
                    }

                    $rooms[$ruangName] = \App\Models\Room::create([
                        'name' => $ruangName,
                        'type' => $type,
                        'capacity' => 40,
                        'is_active' => true
                    ]);
                }

                $assignedRoomId = $rooms[$ruangName]->id;

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
        
        $this->command->info($count . ' Jadwal berhasil di-seed dengan alokasi ruangan nyata dari PDF serta sync Mata Kuliah dan Kelas.');
    }
}
