<?php
$baseDir = 'c:/xampp/htdocs/pergantiandosen';
$entities = [
    'Dosen' => ['model' => 'App\Models\User', 'view_path' => 'admin.dosen', 'controller_path' => 'app/Http/Controllers/Admin/DosenController.php', 'name_space' => 'App\Http\Controllers\Admin', 'vars' => 'dosens', 'var' => 'dosen', 'query' => "User::where('role', 'dosen')->get()"],
    'Mahasiswa' => ['model' => 'App\Models\Mahasiswa', 'view_path' => 'admin.mahasiswa', 'controller_path' => 'app/Http/Controllers/Admin/MahasiswaController.php', 'name_space' => 'App\Http\Controllers\Admin', 'vars' => 'mahasiswas', 'var' => 'mahasiswa', 'query' => "Mahasiswa::all()"],
    'MataKuliah' => ['model' => 'App\Models\MataKuliah', 'view_path' => 'admin.matakuliah', 'controller_path' => 'app/Http/Controllers/MataKuliahController.php', 'name_space' => 'App\Http\Controllers', 'vars' => 'matakuliahs', 'var' => 'matakuliah', 'query' => "MataKuliah::all()"],
    'Kelas' => ['model' => 'App\Models\Kelas', 'view_path' => 'admin.kelas', 'controller_path' => 'app/Http/Controllers/KelasController.php', 'name_space' => 'App\Http\Controllers', 'vars' => 'kelas', 'var' => 'kelas_item', 'query' => "Kelas::all()"],
    'Room' => ['model' => 'App\Models\Room', 'view_path' => 'admin.room', 'controller_path' => 'app/Http/Controllers/Admin/RoomController.php', 'name_space' => 'App\Http\Controllers\Admin', 'vars' => 'rooms', 'var' => 'room', 'query' => "Room::all()"],
    'KalenderAkademik' => ['model' => 'App\Models\KalenderAkademik', 'view_path' => 'admin.kalender', 'controller_path' => 'app/Http/Controllers/KalenderAkademikController.php', 'name_space' => 'App\Http\Controllers', 'vars' => 'kalenders', 'var' => 'kalender', 'query' => "KalenderAkademik::all()"]
];

foreach ($entities as $name => $data) {
    $storeLogic = "";
    if ($name == 'Dosen') {
        $storeLogic = "
        \$data = \$request->all();
        \$data['role'] = 'dosen';
        \$data['password'] = bcrypt('password');
        User::create(\$data);
        ";
    } else {
        $storeLogic = "\\{$data['model']}::create(\$request->all());";
    }

    $emailField = "";
    if ($name == 'Dosen') {
        $emailField = "
                    <div class=\"mb-6\">
                        <label class=\"block text-gray-700 text-sm font-bold mb-2\">Email</label>
                        <input type=\"email\" name=\"email\" value=\"{{ \${$data['var']}->email ?? '' }}\" class=\"shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500\">
                    </div>";
    }

    $fieldName = 'nama';
    if ($name == 'Dosen') $fieldName = 'name';
    if ($name == 'Kelas') $fieldName = 'nama_kelas';
    if ($name == 'Room') $fieldName = 'kode_ruangan';

    $controllerContent = "<?php
namespace {$data['name_space']};

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use {$data['model']};

class {$name}Controller extends Controller
{
    public function index()
    {
        \${$data['vars']} = {$data['query']};
        return view('{$data['view_path']}.index', compact('{$data['vars']}'));
    }

    public function create()
    {
        return view('{$data['view_path']}.create');
    }

    public function store(Request \$request)
    {
        " . $storeLogic . "
        return redirect()->route('{$data['view_path']}.index')->with('success', 'Data created successfully');
    }

    public function edit(\$id)
    {
        \${$data['var']} = \\{$data['model']}::findOrFail(\$id);
        return view('{$data['view_path']}.edit', compact('{$data['var']}'));
    }

    public function update(Request \$request, \$id)
    {
        \${$data['var']} = \\{$data['model']}::findOrFail(\$id);
        \${$data['var']}->update(\$request->all());
        return redirect()->route('{$data['view_path']}.index')->with('success', 'Data updated successfully');
    }

    public function destroy(\$id)
    {
        \${$data['var']} = \\{$data['model']}::findOrFail(\$id);
        \${$data['var']}->delete();
        return redirect()->route('{$data['view_path']}.index')->with('success', 'Data deleted successfully');
    }
}
";
    $controllerDir = dirname($baseDir . '/' . $data['controller_path']);
    if (!is_dir($controllerDir)) {
        mkdir($controllerDir, 0777, true);
    }
    file_put_contents($baseDir . '/' . $data['controller_path'], $controllerContent);

    $viewDir = $baseDir . '/resources/views/' . str_replace('.', '/', $data['view_path']);
    if (!is_dir($viewDir)) {
        mkdir($viewDir, 0777, true);
    }

    $indexView = "
<x-app-layout>
    <x-slot name=\"header\">
        <div class=\"flex justify-between\">
            <h2 class=\"font-semibold text-xl text-gray-800 leading-tight\">
                Kelola {$name}
            </h2>
            <a href=\"{{ route('{$data['view_path']}.create') }}\" class=\"bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm font-medium transition\">Tambah {$name}</a>
        </div>
    </x-slot>

    <div class=\"py-12\">
        <div class=\"max-w-7xl mx-auto sm:px-6 lg:px-8\">
            <div class=\"bg-white shadow-sm sm:rounded-xl overflow-hidden p-6 border border-gray-100\">
                @if(session('success'))
                    <div class=\"bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg\" role=\"alert\">
                        <p class=\"font-medium\">{{ session('success') }}</p>
                    </div>
                @endif
                <div class=\"overflow-x-auto\">
                    <table class=\"min-w-full leading-normal\">
                        <thead>
                            <tr>
                                <th class=\"px-5 py-4 border-b-2 border-gray-100 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider\">
                                    ID
                                </th>
                                <th class=\"px-5 py-4 border-b-2 border-gray-100 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider\">
                                    Nama / Label
                                </th>
                                <th class=\"px-5 py-4 border-b-2 border-gray-100 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider\">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class=\"divide-y divide-gray-100\">
                            @foreach(\${$data['vars']} as \$item)
                                <tr class=\"hover:bg-gray-50 transition-colors\">
                                    <td class=\"px-5 py-4 bg-white text-sm text-gray-700\">
                                        {{ \$item->id }}
                                    </td>
                                    <td class=\"px-5 py-4 bg-white text-sm font-medium text-gray-900\">
                                        {{ \$item->name ?? \$item->nama ?? \$item->nama_kelas ?? \$item->kode_ruangan ?? \$item->judul ?? 'Data' }}
                                    </td>
                                    <td class=\"px-5 py-4 bg-white text-sm\">
                                        <div class=\"flex space-x-3\">
                                            <a href=\"{{ route('{$data['view_path']}.edit', \$item->id) }}\" class=\"text-blue-600 hover:text-blue-900 font-medium transition\">Edit</a>
                                            <form action=\"{{ route('{$data['view_path']}.destroy', \$item->id) }}\" method=\"POST\" onsubmit=\"return confirm('Yakin ingin menghapus?');\" class=\"inline\">
                                                @csrf
                                                @method('DELETE')
                                                <button type=\"submit\" class=\"text-red-600 hover:text-red-900 font-medium transition\">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count(\${$data['vars']}) == 0)
                    <div class=\"text-center py-10 text-gray-500\">
                        Belum ada data.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>";
    file_put_contents($viewDir . '/index.blade.php', $indexView);

    $formView = "
<x-app-layout>
    <x-slot name=\"header\">
        <h2 class=\"font-semibold text-xl text-gray-800 leading-tight\">
            {{ isset(\${$data['var']}) ? 'Edit' : 'Tambah' }} {$name}
        </h2>
    </x-slot>

    <div class=\"py-12\">
        <div class=\"max-w-7xl mx-auto sm:px-6 lg:px-8\">
            <div class=\"bg-white overflow-hidden shadow-sm sm:rounded-xl p-8 border border-gray-100 max-w-2xl mx-auto\">
                <form action=\"{{ isset(\${$data['var']}) ? route('{$data['view_path']}.update', \${$data['var']}->id) : route('{$data['view_path']}.store') }}\" method=\"POST\">
                    @csrf
                    @if(isset(\${$data['var']}))
                        @method('PUT')
                    @endif

                    <div class=\"mb-6\">
                        <label class=\"block text-gray-700 text-sm font-bold mb-2\">Nama / Label / Judul / Kode</label>
                        <input type=\"text\" name=\"{$fieldName}\" value=\"{{ \${$data['var']}->name ?? \${$data['var']}->nama ?? \${$data['var']}->nama_kelas ?? \${$data['var']}->kode_ruangan ?? \${$data['var']}->judul ?? '' }}\" class=\"shadow-sm border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:ring-blue-500 focus:border-blue-500\">
                    </div>

                    " . $emailField . "

                    <div class=\"flex items-center justify-end mt-8\">
                        <a href=\"{{ route('{$data['view_path']}.index') }}\" class=\"text-gray-500 hover:text-gray-700 mr-4 font-medium\">Batal</a>
                        <button type=\"submit\" class=\"bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2\">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>";
    file_put_contents($viewDir . '/create.blade.php', $formView);
    file_put_contents($viewDir . '/edit.blade.php', $formView);
}
echo "Scaffolding Complete.\n";
