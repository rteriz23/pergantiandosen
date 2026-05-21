<?php
$nengSusi = App\Models\User::where('name', 'like', '%Neng Susi%')->first();
$memet = App\Models\User::where('name', 'like', '%Memet%')->first();

if ($nengSusi && $memet) {
    $updated = App\Models\Schedule::where('user_id', $nengSusi->id)
                ->where('mata_kuliah', 'like', '%Digital Marketing%')
                ->update(['user_id' => $memet->id]);
    echo "Success. Updated " . $updated . " schedules to Memet Sanjaya.\n";
} else {
    echo "Dosen not found\n";
}
