<?php
$cindy = App\Models\User::where('name', 'like', '%Cindy%')->first();
$room305 = App\Models\Room::where('name', 'like', '%305%')->first();

if (!$cindy || !$room305) {
    echo "Dosen or Room not found!\n";
    exit(1);
}

// 1. Delete all existing schedules for Visualisasi Data 2ABAS
$deleted = App\Models\Schedule::where('kelas', '2ABAS')
    ->where('mata_kuliah', 'like', '%Visualisasi%')
    ->delete();

echo "Deleted $deleted existing 2ABAS Visualisasi Data schedules.\n";

// 2. Define the exact Thursday dates for all 16 meetings
$meetings = [
    1 => '2026-03-12', // Moved from Friday 2026-03-13
    2 => '2026-04-02', // Keep Thursday 2026-04-02
    3 => '2026-04-09', // Moved from Friday 2026-04-10
    4 => '2026-04-16', // Keep Thursday
    5 => '2026-04-23', // Keep Thursday
    6 => '2026-04-30', // Keep Thursday
    7 => '2026-05-07', // Keep Thursday
    8 => '2026-05-14', // Keep Thursday
    9 => '2026-05-21', // Keep Thursday
    10 => '2026-05-28', // Keep Thursday
    11 => '2026-06-04', // Keep Thursday
    12 => '2026-06-11', // Keep Thursday
    13 => '2026-06-18', // Keep Thursday
    14 => '2026-06-25', // Keep Thursday
    15 => '2026-07-02', // Keep Thursday
    16 => '2026-07-09'  // Keep Thursday
];

$created = 0;
foreach ($meetings as $pert => $date) {
    App\Models\Schedule::create([
        'user_id' => $cindy->id,
        'prodi_id' => 1,
        'mata_kuliah' => '1BS10023 - Visualisasi Data (2 SKS)',
        'kelas' => '2ABAS',
        'pertemuan' => $pert,
        'waktu_mulai' => $date . ' 08:40:00',
        'waktu_selesai' => $date . ' 10:50:00',
        'room_id' => $room305->id,
        'status' => 'Selesai',
        'periode' => '2025 Genap'
    ]);
    $created++;
}

echo "Created $created clean Thursday 2ABAS Visualisasi Data schedules in Room 305.\n";

// Verify
echo "\n=== Verifikasi Jadwal Baru 2ABAS ===\n";
$new_schedules = App\Models\Schedule::where('kelas', '2ABAS')
    ->where('mata_kuliah', 'like', '%Visualisasi%')
    ->orderBy('pertemuan')
    ->get();

foreach ($new_schedules as $s) {
    $dayName = \Carbon\Carbon::parse($s->waktu_mulai)->format('l (Y-m-d H:i)');
    echo "Pert: {$s->pertemuan} | {$dayName} | Ruang: " . ($s->room->name ?? 'N/A') . "\n";
}
