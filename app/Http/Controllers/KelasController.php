<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kelas;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Kelas::query();

        if ($search) {
            $query->where('nama_kelas', 'like', "%{$search}%");
        }

        $kelas = $query->paginate(10)->withQueryString();

        return view('admin.kelas.index', compact('kelas', 'search'));
    }

    public function create()
    {
        return view('admin.kelas.create');
    }

    public function store(Request $request)
    {
        Kelas::create($request->all());
        return redirect()->route('admin.kelas.index')->with('success', 'Data created successfully');
    }

    public function edit($id)
    {
        $kelas_item = Kelas::findOrFail($id);
        return view('admin.kelas.edit', compact('kelas_item'));
    }

    public function update(Request $request, $id)
    {
        $kelas_item = Kelas::findOrFail($id);
        $kelas_item->update($request->all());
        return redirect()->route('admin.kelas.index')->with('success', 'Data updated successfully');
    }

    public function destroy($id)
    {
        $kelas_item = Kelas::findOrFail($id);
        $kelas_item->delete();
        return redirect()->route('admin.kelas.index')->with('success', 'Data deleted successfully');
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
            if (count($row) < 1 || empty($row[0])) continue;
            
            $nama_kelas = $row[0];

            Kelas::updateOrCreate(
                ['nama_kelas' => $nama_kelas]
            );
            $importedCount++;
        }
        fclose($handle);

        return redirect()->route('admin.kelas.index')->with('success', "$importedCount data kelas berhasil diimport.");
    }
}
