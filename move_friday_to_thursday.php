<?php
// Pindahkan jadwal Visualisasi Data 2ABAS dari Jumat ke Kamis
// SQLite: strftime '%w' 5 = Jumat -> pindah ke Kamis (kurangi 1 hari)

$schedules = App\Models\Schedule::where('kelas', '2ABAS')
    ->where('mata_kuliah', 'like', '%Visualisasi Data%')
    ->whereRaw("strftime('%w', waktu_mulai) = '5'") // 5 = Jumat
    ->get();

echo "Ditemukan " . $schedules->count() . " jadwal Visualisasi Data 2ABAS di hari Jumat.\n";

$updated = 0;
foreach ($schedules as $s) {
    $new_mulai = \Carbon\Carbon::parse($s->waktu_mulai)->subDay();
    $new_selesai = \Carbon\Carbon::parse($s->waktu_selesai)->subDay();
    $s->waktu_mulai = $new_mulai;
    $s->waktu_selesai = $new_selesai;
    $s->save();
    $updated++;
}
echo "Berhasil dipindah ke Kamis: $updated jadwal\n";

// Verifikasi
$check = App\Models\Schedule::where('kelas', '2ABAS')
    ->where('mata_kuliah', 'like', '%Visualisasi Data%')
    ->whereRaw("strftime('%w', waktu_mulai) = '5'")
    ->count();
echo "Sisa di Jumat: $check\n";

$kamis = App\Models\Schedule::where('kelas', '2ABAS')
    ->where('mata_kuliah', 'like', '%Visualisasi Data%')
    ->whereRaw("strftime('%w', waktu_mulai) = '4'")
    ->count();
echo "Total di Kamis sekarang: $kamis\n";
