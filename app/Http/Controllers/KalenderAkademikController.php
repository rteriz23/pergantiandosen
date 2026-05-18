<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KalenderAkademik;

class KalenderAkademikController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = KalenderAkademik::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $kalenders = $query->orderBy('tanggal_mulai', 'desc')->paginate(10)->withQueryString();

        return view('admin.kalender.index', compact('kalenders', 'search'));
    }

    public function create()
    {
        return view('admin.kalender.create');
    }

    public function store(Request $request)
    {
        KalenderAkademik::create($request->all());
        return redirect()->route('admin.kalender.index')->with('success', 'Data created successfully');
    }

    public function edit($id)
    {
        $kalender = KalenderAkademik::findOrFail($id);
        return view('admin.kalender.edit', compact('kalender'));
    }

    public function update(Request $request, $id)
    {
        $kalender = KalenderAkademik::findOrFail($id);
        $kalender->update($request->all());
        return redirect()->route('admin.kalender.index')->with('success', 'Data updated successfully');
    }

    public function destroy($id)
    {
        $kalender = KalenderAkademik::findOrFail($id);
        $kalender->delete();
        return redirect()->route('admin.kalender.index')->with('success', 'Data deleted successfully');
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
            
            $judul = $row[0];
            $tanggal_mulai = $row[1];
            $tanggal_selesai = $row[2];
            $keterangan = $row[3] ?? '';
            $warna = $row[4] ?? '#4F46E5';

            KalenderAkademik::create([
                'judul' => $judul,
                'tanggal_mulai' => $tanggal_mulai,
                'tanggal_selesai' => $tanggal_selesai,
                'keterangan' => $keterangan,
                'warna' => $warna
            ]);
            $importedCount++;
        }
        fclose($handle);

        return redirect()->route('admin.kalender.index')->with('success', "$importedCount agenda kalender akademik berhasil diimport.");
    }

    public function export(Request $request)
    {
        $search = $request->get('search');

        $query = KalenderAkademik::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $kalenders = $query->orderBy('tanggal_mulai', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="master_kalender_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($kalenders) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['judul', 'tanggal_mulai', 'tanggal_selesai', 'keterangan', 'warna'], ';');
            
            // Rows
            foreach ($kalenders as $k) {
                fputcsv($file, [$k->judul, $k->tanggal_mulai, $k->tanggal_selesai, $k->keterangan ?? '', $k->warna ?? '#4F46E5'], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
