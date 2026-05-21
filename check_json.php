<?php
$json = file_get_contents('c:\xampp\htdocs\pergantiandosen\database\seeders\data\schedules.json');
$data = json_decode($json, true);

$matching = [];
foreach ($data as $item) {
    if (isset($item['kelas']) && $item['kelas'] === '2ABAS' && strpos($item['mata_kuliah'], 'Visualisasi') !== false) {
        $matching[] = $item;
    }
}

echo "Total di json: " . count($matching) . "\n";
foreach ($matching as $m) {
    echo "Pertemuan: {$m['pertemuan']} | Waktu: {$m['waktu_mulai']} | Ruang: {$m['ruang']}\n";
}
