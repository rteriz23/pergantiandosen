<?php
$baseDir = 'c:/xampp/htdocs/pergantiandosen';
$entities = [
    'Dosen' => ['controller' => 'app/Http/Controllers/Admin/DosenController.php', 'view' => 'resources/views/admin/dosen/index.blade.php', 'var' => 'dosens'],
    'Mahasiswa' => ['controller' => 'app/Http/Controllers/Admin/MahasiswaController.php', 'view' => 'resources/views/admin/mahasiswa/index.blade.php', 'var' => 'mahasiswas'],
    'MataKuliah' => ['controller' => 'app/Http/Controllers/MataKuliahController.php', 'view' => 'resources/views/admin/matakuliah/index.blade.php', 'var' => 'matakuliahs'],
    'Kelas' => ['controller' => 'app/Http/Controllers/KelasController.php', 'view' => 'resources/views/admin/kelas/index.blade.php', 'var' => 'kelas'],
    'Room' => ['controller' => 'app/Http/Controllers/Admin/RoomController.php', 'view' => 'resources/views/admin/room/index.blade.php', 'var' => 'rooms'],
    'KalenderAkademik' => ['controller' => 'app/Http/Controllers/KalenderAkademikController.php', 'view' => 'resources/views/admin/kalender/index.blade.php', 'var' => 'kalenders']
];

foreach ($entities as $name => $data) {
    // 1. Modify Controller
    $controllerPath = $baseDir . '/' . $data['controller'];
    if (file_exists($controllerPath)) {
        $content = file_get_contents($controllerPath);
        // Replace ::all() with ::paginate(10)
        $content = preg_replace('/::all\(\)/', '::paginate(10)', $content);
        // Replace ->get() with ->paginate(10)
        $content = preg_replace('/->get\(\)/', '->paginate(10)', $content);
        file_put_contents($controllerPath, $content);
    }

    // 2. Modify View
    $viewPath = $baseDir . '/' . $data['view'];
    if (file_exists($viewPath)) {
        $content = file_get_contents($viewPath);
        // Add pagination links after the table div, before the if(count == 0) block
        $linksCode = "\n                <div class=\"mt-4\">\n                    {{ \$" . $data['var'] . "->links() }}\n                </div>";
        
        // Let's insert it right after </div> (for overflow-x-auto)
        // Since we are not sure exactly, let's insert it before the closing </div> of the card.
        // Or replace `@if(count(\$` with `{{ \$var->links() }} \n @if(count(\$`
        // The count() will fail on LengthAwarePaginator in some cases if we use it, but count($paginator) returns items count on current page.
        // Let's just find `</div>\n                @if(count(` and inject between them
        if (strpos($content, $linksCode) === false) { // check if not already added
            $content = str_replace('</table>
                </div>', '</table>
                </div>' . $linksCode, $content);
            file_put_contents($viewPath, $content);
        }
    }
}
echo "Pagination added.\n";
