<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mahasiswa;
use App\Models\Schedule;
use App\Models\MahasiswaJadwal;

class SyncMahasiswaJadwal extends Command
{
    protected $signature = 'mahasiswa:sync-jadwal {--periode= : Filter by periode} {--nim= : Sync only for one NIM}';
    protected $description = 'Sync (auto-populate) tabel mahasiswa_jadwal dari data schedules berdasarkan kelas mahasiswa';

    public function handle()
    {
        $periode = $this->option('periode');
        $nim     = $this->option('nim');

        $query = Mahasiswa::query();
        if ($nim) {
            $query->where('nim', $nim);
        }
        $mahasiswas = $query->whereNotNull('kelas')->get();

        if ($mahasiswas->isEmpty()) {
            $this->warn('Tidak ada mahasiswa dengan data kelas ditemukan.');
            return 0;
        }

        $inserted = 0;
        $skipped  = 0;

        $this->info("Memproses {$mahasiswas->count()} mahasiswa...");
        $bar = $this->output->createProgressBar($mahasiswas->count());
        $bar->start();

        foreach ($mahasiswas as $mhs) {
            // Cari semua jadwal yang kelasnya cocok dengan mahasiswa ini
            $scheduleQuery = Schedule::where('kelas', $mhs->kelas);
            if ($periode) {
                $scheduleQuery->where('periode', $periode);
            }
            $schedules = $scheduleQuery->get();

            foreach ($schedules as $schedule) {
                $tipe = $mhs->status_mengulang ? 'pengulang' : 'reguler';

                $exists = MahasiswaJadwal::where('mahasiswa_id', $mhs->id)
                                         ->where('schedule_id', $schedule->id)
                                         ->exists();
                if (!$exists) {
                    MahasiswaJadwal::create([
                        'mahasiswa_id'    => $mhs->id,
                        'schedule_id'     => $schedule->id,
                        'tipe_enrollment' => $tipe,
                        'periode'         => $schedule->periode,
                    ]);
                    $inserted++;
                } else {
                    $skipped++;
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai! {$inserted} enrollment baru ditambahkan, {$skipped} sudah ada (dilewati).");

        return 0;
    }
}
