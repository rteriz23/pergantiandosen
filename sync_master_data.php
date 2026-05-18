<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\Schedule;

$schedules = Schedule::all();
$mkCount = 0;
$kelasCount = 0;

foreach ($schedules as $s) {
    if (!empty($s->mata_kuliah)) {
        $parts = explode(' - ', $s->mata_kuliah, 2);
        $kode = count($parts) > 1 ? trim($parts[0]) : '-';
        
        $mk = MataKuliah::where('kode', $kode)->first();
        if (!$mk) {
            try {
                MataKuliah::create(['kode' => $kode, 'nama' => $s->mata_kuliah]);
                $mkCount++;
            } catch (\Exception $e) {
                // Ignore if still duplicate somehow
            }
        }
    }
    if (!empty($s->kelas)) {
        $kls = Kelas::firstOrCreate(['nama_kelas' => $s->kelas]);
        if ($kls->wasRecentlyCreated) $kelasCount++;
    }
}

echo "Created $mkCount Mata Kuliah and $kelasCount Kelas dari tabel jadwal.\n";
