<?php
$schedules = App\Models\Schedule::where('kelas', '2ABAS')
    ->where('mata_kuliah', 'like', '%Visualisasi%')
    ->orderBy('waktu_mulai')
    ->get();

foreach ($schedules as $s) {
    $dayName = \Carbon\Carbon::parse($s->waktu_mulai)->format('l (Y-m-d H:i)');
    echo "ID: {$s->id} | Day: {$dayName} | Ruang: " . ($s->room->name ?? 'N/A') . "\n";
}
