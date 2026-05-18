<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Schedule;
use Illuminate\Support\Facades\File;

$dataDir = database_path('seeders/data');
if (!File::exists($dataDir)) {
    File::makeDirectory($dataDir, 0755, true);
}

// Dump Dosen Users
$dosen = User::with('prodi')->where('role', 'dosen')->get()->map(function($user) {
    $arr = $user->makeVisible(['password'])->makeHidden(['id', 'prodi_id', 'created_at', 'updated_at'])->toArray();
    $arr['prodi_name'] = $user->prodi->name ?? null;
    return $arr;
});
File::put($dataDir . '/dosen.json', collect($dosen)->toJson(JSON_PRETTY_PRINT));

// Dump Schedules
// We need to map user_id back to something we can identify (like email) 
// or just keep the raw data. Since we want a robust seeder, mapping to Dosen email is safer
$schedules = Schedule::with(['dosen', 'prodi'])->get()->map(function($schedule) {
    return [
        'dosen_email' => $schedule->dosen->email ?? null,
        'prodi_name' => $schedule->prodi->name ?? null,
        'periode' => $schedule->periode,
        'mata_kuliah' => $schedule->mata_kuliah,
        'kelas' => $schedule->kelas,
        'pertemuan' => $schedule->pertemuan,
        'waktu_mulai' => $schedule->waktu_mulai,
        'waktu_selesai' => $schedule->waktu_selesai,
        'status' => $schedule->status,
    ];
});
File::put($dataDir . '/schedules.json', $schedules->toJson(JSON_PRETTY_PRINT));

echo "Data berhasil didump ke JSON.\n";
