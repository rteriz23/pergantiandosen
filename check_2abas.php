<?php
$schedules = App\Models\Schedule::where('kelas', 'like', '%2AB%')
    ->where('mata_kuliah', 'like', '%Visualisasi Data%')
    ->get();

foreach ($schedules as $s) {
    echo "ID: " . $s->id . " | Dosen: " . ($s->dosen->name ?? 'N/A') . " | Kelas: " . $s->kelas . " | Waktu: " . $s->waktu_mulai . " | Ruang: " . ($s->room->name ?? 'N/A') . "\n";
}
