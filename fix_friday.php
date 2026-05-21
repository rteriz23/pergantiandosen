<?php
// Hapus jadwal Visualisasi Data kelas 2ABAS di hari Jumat
// SQLite: gunakan strftime untuk hari, 5 = Jumat
$del4 = App\Models\Schedule::where('kelas', '2ABAS')
    ->where('mata_kuliah', 'like', '%Visualisasi Data%')
    ->whereRaw("strftime('%w', waktu_mulai) = '5'") // 5 = Jumat di SQLite
    ->delete();
echo "Deleted Visualisasi Data 2ABAS Jumat: $del4\n";

// Cek apakah masih ada
$remaining = App\Models\Schedule::where('kelas', '2ABAS')
    ->where('mata_kuliah', 'like', '%Visualisasi Data%')
    ->count();
echo "Remaining Visualisasi Data 2ABAS: $remaining\n";

// Verifikasi ringkasan setelah fix
echo "\n=== Verifikasi Jadwal Cindy di Hari Kamis (Unik) ===\n";
$cindy = App\Models\User::where('name', 'like', '%Cindy%')->first();
$rows = \DB::select("
    SELECT DISTINCT
        time(waktu_mulai) as jam_mulai,
        time(waktu_selesai) as jam_selesai,
        mata_kuliah,
        kelas,
        room_id
    FROM schedules
    WHERE user_id = ? AND strftime('%w', waktu_mulai) = '4'
    ORDER BY waktu_mulai
", [$cindy->id]);

foreach ($rows as $r) {
    $room = App\Models\Room::find($r->room_id);
    echo $r->jam_mulai . ' - ' . $r->jam_selesai . ' | ' . $r->mata_kuliah . ' | ' . $r->kelas . ' | ' . ($room->name ?? 'N/A') . "\n";
}
