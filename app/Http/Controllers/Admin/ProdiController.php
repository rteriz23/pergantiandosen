<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prodi;

class ProdiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Prodi::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $prodis = $query->paginate(10)->withQueryString();

        return view('admin.prodi.index', compact('prodis', 'search'));
    }

    public function create()
    {
        return view('admin.prodi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:prodis,name|max:255',
        ]);

        Prodi::create($request->all());

        return redirect()->route('admin.prodi.index')->with('success', 'Program Studi berhasil dibuat.');
    }

    public function edit($id)
    {
        $prodi = Prodi::findOrFail($id);
        return view('admin.prodi.edit', compact('prodi'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:prodis,name,' . $id,
        ]);

        $prodi = Prodi::findOrFail($id);
        $prodi->update($request->all());

        return redirect()->route('admin.prodi.index')->with('success', 'Program Studi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $prodi = Prodi::findOrFail($id);
        $prodi->delete();

        return redirect()->route('admin.prodi.index')->with('success', 'Program Studi berhasil dihapus.');
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
        // fallback to comma if only 1 column parsed and contains comma
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
            
            $name = $row[0];

            Prodi::updateOrCreate(
                ['name' => $name]
            );
            $importedCount++;
        }
        fclose($handle);

        return redirect()->route('admin.prodi.index')->with('success', "$importedCount data program studi berhasil diimport.");
    }

    public function export(Request $request)
    {
        $search = $request->get('search');

        $query = Prodi::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $prodis = $query->orderBy('name')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="master_program_studi_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($prodis) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['name'], ';');
            
            // Rows
            foreach ($prodis as $p) {
                fputcsv($file, [$p->name], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
