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
        
        Schedule::truncate(); // Hapus jadwal lama agar tidak duplikat
        
        $count = 0;
        foreach ($scheduleData as $data) {
            $dosen = User::where('email', $data['dosen_email'])->first();
            $prodiName = $data['prodi_name'] ?? 'Umum';
            $prodi = \App\Models\Prodi::firstOrCreate(['name' => $prodiName]);
            
            if ($dosen) {
                Schedule::create([
                    'user_id' => $dosen->id,
                    'prodi_id' => $prodi->id,
                    'periode' => $data['periode'],
                    'mata_kuliah' => $data['mata_kuliah'],
                    'kelas' => $data['kelas'],
                    'pertemuan' => $data['pertemuan'],
                    'waktu_mulai' => $data['waktu_mulai'],
                    'waktu_selesai' => $data['waktu_selesai'],
                    'status' => $data['status'],
                ]);
                $count++;
            }
        }
        
        $this->command->info($count . ' Jadwal berhasil di-seed.');
    }
}
