<?php
// 1. Hapus data double yang saya tambahkan manual untuk Cindy di hari Kamis
//    (yang tanpa kode mata kuliah, kelas '1SI-1')
$cindy = App\Models\User::where('name', 'like', '%Cindy%')->first();

// Hapus "Pengantar Pemrograman dan Paradigma OO" (1SI-1) - duplikat dari yang bercode 1TI25026
$del1 = App\Models\Schedule::where('user_id', $cindy->id)
    ->where('mata_kuliah', 'Pengantar Pemrograman dan Paradigma OO')
    ->where('kelas', '1SI-1')
    ->delete();
echo "Deleted Pengantar Pemrograman duplikat: $del1\n";

// Hapus "Pemrograman Objek Orianted" (1SI-1) - duplikat dari yang bercode 1TI25027
$del2 = App\Models\Schedule::where('user_id', $cindy->id)
    ->where('mata_kuliah', 'Pemrograman Objek Orianted')
    ->where('kelas', '1SI-1')
    ->delete();
echo "Deleted Pemrograman OO duplikat: $del2\n";

// Hapus "Visualisasi Data" (2AB+2AS) yang saya tambahkan manual
$del3 = App\Models\Schedule::where('user_id', $cindy->id)
    ->where('mata_kuliah', 'Visualisasi Data')
    ->where('kelas', '2AB+2AS')
    ->delete();
echo "Deleted Visualisasi Data duplikat: $del3\n";

// 2. Hapus jadwal Visualisasi Data kelas 2ABAS di hari Jumat
$del4 = App\Models\Schedule::where('kelas', '2ABAS')
    ->where('mata_kuliah', 'like', '%Visualisasi Data%')
    ->whereRaw('DAYOFWEEK(waktu_mulai) = 6') // 6 = Jumat
    ->delete();
echo "Deleted Visualisasi Data 2ABAS Jumat: $del4\n";

// 3. Verifikasi sisa jadwal Cindy di hari Kamis
echo "\n=== Sisa Jadwal Cindy di Hari Kamis ===\n";
$cindy_thu = App\Models\Schedule::with('room')
    ->where('user_id', $cindy->id)
    ->whereRaw('DAYOFWEEK(waktu_mulai) = 5') // 5 = Kamis
    ->orderBy('waktu_mulai')
    ->get()
    ->unique(function ($s) {
        return $s->mata_kuliah . $s->kelas . date('H:i', strtotime($s->waktu_mulai));
    });

foreach ($cindy_thu as $s) {
    echo date('H:i', strtotime($s->waktu_mulai)) . ' - ' . date('H:i', strtotime($s->waktu_selesai));
    echo ' | ' . $s->mata_kuliah;
    echo ' | Kelas: ' . $s->kelas;
    echo ' | Ruang: ' . ($s->room->name ?? 'N/A') . "\n";
}

echo "\nTotal jadwal Cindy: " . App\Models\Schedule::where('user_id', $cindy->id)->count();
