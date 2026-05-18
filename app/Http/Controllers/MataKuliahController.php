<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MataKuliah;
use App\Models\Prodi;

class MataKuliahController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $prodiId = $request->get('prodi_id');

        $query = MataKuliah::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        if ($prodiId) {
            $query->where('prodi_id', $prodiId);
        }

        $matakuliahs = $query->paginate(10)->withQueryString();
        $prodis = Prodi::all();

        return view('admin.matakuliah.index', compact('matakuliahs', 'prodis', 'search', 'prodiId'));
    }

    public function create()
    {
        return view('admin.matakuliah.create');
    }

    public function store(Request $request)
    {
        MataKuliah::create($request->all());
        return redirect()->route('admin.matakuliah.index')->with('success', 'Data created successfully');
    }

    public function edit($id)
    {
        $matakuliah = MataKuliah::findOrFail($id);
        return view('admin.matakuliah.edit', compact('matakuliah'));
    }

    public function update(Request $request, $id)
    {
        $matakuliah = MataKuliah::findOrFail($id);
        $matakuliah->update($request->all());
        return redirect()->route('admin.matakuliah.index')->with('success', 'Data updated successfully');
    }

    public function destroy($id)
    {
        $matakuliah = MataKuliah::findOrFail($id);
        $matakuliah->delete();
        return redirect()->route('admin.matakuliah.index')->with('success', 'Data deleted successfully');
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
            
            $kode = $row[0];
            $nama = $row[1];
            $sks = intval($row[2]);
            $jenis = $row[3] ?? 'Teori';
            $prodiName = $row[4] ?? 'Umum';

            $prodi = Prodi::firstOrCreate(['name' => $prodiName]);

            MataKuliah::updateOrCreate(
                ['kode' => $kode],
                [
                    'nama' => $nama,
                    'sks' => $sks,
                    'jenis' => $jenis,
                    'prodi_id' => $prodi->id,
                    'is_active' => true
                ]
            );
            $importedCount++;
        }
        fclose($handle);

        return redirect()->route('admin.matakuliah.index')->with('success', "$importedCount data mata kuliah berhasil diimport.");
    }
}
