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
                $q->where('name', 'like', "%{$search}%")
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
        return view('admin.mahasiswa.create');
    }

    public function store(Request $request)
    {
        Mahasiswa::create($request->all());
        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data created successfully');
    }

    public function edit($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        return view('admin.mahasiswa.edit', compact('mahasiswa'));
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

            $prodi = Prodi::firstOrCreate(['name' => $prodiName]);

            Mahasiswa::updateOrCreate(
                ['nim' => $nim],
                [
                    'name' => $name,
                    'email' => $email,
                    'prodi_id' => $prodi->id,
                    'kelas' => $kelas
                ]
            );
            $importedCount++;
        }
        fclose($handle);

        return redirect()->route('admin.mahasiswa.index')->with('success', "$importedCount data mahasiswa berhasil diimport.");
    }
}
