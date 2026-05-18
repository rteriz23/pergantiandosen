<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$prodi = App\Models\Prodi::first();
if ($prodi) {
    App\Models\User::create([
        'name' => 'Kaprodi SI',
        'email' => 'kaprodi_si@lpkia.ac.id',
        'password' => bcrypt('password'),
        'role' => 'kaprodi',
        'prodi_id' => $prodi->id
    ]);
    echo "Kaprodi created!\n";
} else {
    echo "No Prodi found.\n";
}
