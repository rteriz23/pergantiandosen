<?php
$cindy = App\Models\User::where('name', 'like', '%Cindy%')->first();
$room305 = App\Models\Room::where('name', 'like', '%305%')->first();
$room109 = App\Models\Room::where('name', 'like', '%109%')->first(); 

if (!$room109) {
    $room109 = App\Models\Room::create(['name' => 'Ruang Teori 109', 'type' => 'kelas', 'capacity' => 40, 'is_active' => true]);
}

$thursdays = [];
$start_date = \Carbon\Carbon::parse('2026-03-12'); // First Thursday
for ($i=0; $i<14; $i++) {
    $thursdays[] = $start_date->copy()->addWeeks($i);
}

foreach ($thursdays as $index => $t) {
    $date_str = $t->format('Y-m-d');
    
    // 1. Visualisasi Data
    App\Models\Schedule::updateOrCreate([
        'user_id' => $cindy->id,
        'mata_kuliah' => 'Visualisasi Data',
        'kelas' => '2AB+2AS',
        'pertemuan' => $index + 1,
    ], [
        'prodi_id' => 1,
        'waktu_mulai' => $date_str . ' 08:40:00',
        'waktu_selesai' => $date_str . ' 10:50:00',
        'room_id' => $room305->id,
        'status' => 'Selesai',
        'periode' => '2025 Genap'
    ]);

    // 2. Pengantar Pemrograman dan Paradigma OO
    App\Models\Schedule::updateOrCreate([
        'user_id' => $cindy->id,
        'mata_kuliah' => 'Pengantar Pemrograman dan Paradigma OO',
        'kelas' => '1SI-1',
        'pertemuan' => $index + 1,
    ], [
        'prodi_id' => 1,
        'waktu_mulai' => $date_str . ' 12:10:00',
        'waktu_selesai' => $date_str . ' 14:20:00',
        'room_id' => $room109->id,
        'status' => 'Selesai',
        'periode' => '2025 Genap'
    ]);

    // 3. Pemrograman Objek Orianted
    App\Models\Schedule::updateOrCreate([
        'user_id' => $cindy->id,
        'mata_kuliah' => 'Pemrograman Objek Orianted',
        'kelas' => '1SI-1',
        'pertemuan' => $index + 1,
    ], [
        'prodi_id' => 1,
        'waktu_mulai' => $date_str . ' 14:30:00',
        'waktu_selesai' => $date_str . ' 16:40:00',
        'room_id' => $room305->id,
        'status' => 'Selesai',
        'periode' => '2025 Genap'
    ]);
}
echo "Schedules for Cindy M.Pd created successfully!\n";
