<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Prodi;

class DosenController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $prodiId = $request->get('prodi_id');

        $query = User::where('role', 'dosen');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nidn', 'like', "%{$search}%");
            });
        }

        if ($prodiId) {
            $query->where('prodi_id', $prodiId);
        }

        $dosens = $query->paginate(10)->withQueryString();
        $prodis = Prodi::all();

        return view('admin.dosen.index', compact('dosens', 'prodis', 'search', 'prodiId'));
    }

    public function create()
    {
        $prodis = Prodi::paginate(10);
        return view('admin.dosen.create', compact('prodis'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['role'] = 'dosen';
        $data['password'] = bcrypt('password');
        User::create($data);
        
        return redirect()->route('admin.dosen.index')->with('success', 'Data created successfully');
    }

    public function edit($id)
    {
        $dosen = User::findOrFail($id);
        $prodis = Prodi::paginate(10);
        return view('admin.dosen.edit', compact('dosen', 'prodis'));
    }

    public function update(Request $request, $id)
    {
        $dosen = User::findOrFail($id);
        $dosen->update($request->all());
        return redirect()->route('admin.dosen.index')->with('success', 'Data updated successfully');
    }

    public function destroy($id)
    {
        $dosen = User::findOrFail($id);
        $dosen->delete();
        return redirect()->route('admin.dosen.index')->with('success', 'Data deleted successfully');
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
            if (count($row) < 3 || empty($row[0])) continue;
            
            $name = $row[0];
            $email = $row[1];
            $nidn = $row[2];
            $prodiName = $row[3] ?? 'Umum';

            $prodi = Prodi::firstOrCreate(['name' => $prodiName]);

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'nidn' => $nidn,
                    'role' => 'dosen',
                    'password' => bcrypt('password'),
                    'prodi_id' => $prodi->id
                ]
            );
            $importedCount++;
        }
        fclose($handle);

        return redirect()->route('admin.dosen.index')->with('success', "$importedCount data dosen berhasil diimport.");
    }

    public function export(Request $request)
    {
        $search = $request->get('search');
        $prodiId = $request->get('prodi_id');

        $query = User::where('role', 'dosen');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nidn', 'like', "%{$search}%");
            });
        }

        if ($prodiId) {
            $query->where('prodi_id', $prodiId);
        }

        $dosens = $query->with('prodi')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="master_dosen_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($dosens) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['name', 'email', 'nidn', 'prodi_name'], ';');
            
            // Rows
            foreach ($dosens as $d) {
                fputcsv($file, [$d->name, $d->email, $d->nidn, optional($d->prodi)->name ?? ''], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
