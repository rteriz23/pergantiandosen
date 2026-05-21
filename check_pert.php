<?php
$schedules = App\Models\Schedule::where('kelas', '2ABAS')
    ->where('mata_kuliah', 'like', '%Visualisasi%')
    ->orderBy('pertemuan')
    ->get();

foreach ($schedules as $s) {
    $dayName = \Carbon\Carbon::parse($s->waktu_mulai)->format('l (Y-m-d H:i)');
    echo "ID: {$s->id} | Pert: {$s->pertemuan} | Day: {$dayName} | Ruang: " . ($s->room->name ?? 'N/A') . "\n";
}
