<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Prodi;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $prodiId = $request->get('prodi_id');

        $query = Mahasiswa::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($prodiId) {
            $query->where('prodi_id', $prodiId);
        }

        $mahasiswas = $query->paginate(10)->withQueryString();
        $prodis = Prodi::all();

        return view('admin.mahasiswa.index', compact('mahasiswas', 'prodis', 'search', 'prodiId'));
    }

    public function create()
    {
        $prodis = Prodi::orderBy('name')->get();
        return view('admin.mahasiswa.create', compact('prodis'));
    }

    public function store(Request $request)
    {
        Mahasiswa::create($request->all());
        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data created successfully');
    }

    public function edit($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $prodis = Prodi::orderBy('name')->get();
        return view('admin.mahasiswa.edit', compact('mahasiswa', 'prodis'));
    }

    public function update(Request $request, $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $mahasiswa->update($request->all());
        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data updated successfully');
    }

    public function destroy($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $mahasiswa->delete();
        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data deleted successfully');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header
        $header = fgetcsv($handle, 1000, ';');
        // fallback to comma
        if (count($header) == 1 && strpos($header[0], ',') !== false) {
            rewind($handle);
            $header = fgetcsv($handle, 1000, ',');
            $delimiter = ',';
        } else {
            $delimiter = ';';
        }
        
        $importedCount = 0;
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            if (count($row) < 3 || empty($row[0])) continue;
            
            $name = $row[0];
            $nim = $row[1];
            $email = $row[2];
            $prodiName = $row[3] ?? 'Umum';
            $kelas = $row[4] ?? '1IF';
            $statusMengulang = isset($row[5]) ? filter_var($row[5], FILTER_VALIDATE_BOOLEAN) : false;

            $prodi = Prodi::firstOrCreate(['name' => $prodiName]);

            Mahasiswa::updateOrCreate(
                ['nim' => $nim],
                [
                    'nama' => $name,
                    'email' => $email,
                    'prodi_id' => $prodi->id,
                    'kelas' => $kelas,
                    'status_mengulang' => $statusMengulang
                ]
            );
            $importedCount++;
        }
        fclose($handle);

        return redirect()->route('admin.mahasiswa.index')->with('success', "$importedCount data mahasiswa berhasil diimport.");
    }

    public function export(Request $request)
    {
        $search = $request->get('search');
        $prodiId = $request->get('prodi_id');

        $query = Mahasiswa::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($prodiId) {
            $query->where('prodi_id', $prodiId);
        }

        $mahasiswas = $query->with('prodi')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="master_mahasiswa_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($mahasiswas) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['nama', 'nim', 'email', 'prodi_name', 'kelas', 'status_mengulang'], ';');
            
            // Rows
            foreach ($mahasiswas as $m) {
                fputcsv($file, [$m->nama, $m->nim, $m->email, optional($m->prodi)->name ?? '', $m->kelas ?? '', $m->status_mengulang ? '1' : '0'], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
