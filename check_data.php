<?php
$dosens = App\Models\User::where('role','dosen')->orderBy('name')->get(['id','name']);
foreach($dosens as $d) {
    $count = App\Models\Schedule::where('user_id', $d->id)->count();
    echo $d->name . ' => ' . $count . " jadwal\n";
}
echo "\nTotal dosen: " . $dosens->count();
echo "\nTotal schedules: " . App\Models\Schedule::count();
echo "\nTotal rooms: " . App\Models\Room::count();
