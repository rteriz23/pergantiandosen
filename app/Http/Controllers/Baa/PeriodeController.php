<?php

namespace App\Http\Controllers\Baa;

use App\Http\Controllers\Controller;
use App\Models\Periode;
use Illuminate\Http\Request;

class PeriodeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Periode::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $periodes = $query->orderBy('name', 'desc')->get();
        return view('baa.periodes.index', compact('periodes', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:periodes,name',
        ]);

        Periode::create([
            'name' => $request->name,
            'is_active' => true,
        ]);

        return back()->with('success', 'Periode berhasil ditambahkan.');
    }

    public function update(Request $request, Periode $periode)
    {
        $request->validate([
            'name' => 'required|string|unique:periodes,name,' . $periode->id,
        ]);

        $periode->update([
            'name' => $request->name,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Periode berhasil diperbarui.');
    }

    public function destroy(Periode $periode)
    {
        $periode->delete();
        return back()->with('success', 'Periode berhasil dihapus.');
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
            
            $name = $row[0];

            Periode::updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
            $importedCount++;
        }
        fclose($handle);

        return back()->with('success', "$importedCount data periode berhasil diimport.");
    }
}
