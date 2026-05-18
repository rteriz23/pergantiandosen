<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $type = $request->get('type');

        $query = Room::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        $rooms = $query->paginate(10)->withQueryString();

        return view('admin.room.index', compact('rooms', 'search', 'type'));
    }

    public function create()
    {
        return view('admin.room.create');
    }

    public function store(Request $request)
    {
        Room::create($request->all());
        return redirect()->route('admin.room.index')->with('success', 'Data created successfully');
    }

    public function edit($id)
    {
        $room = Room::findOrFail($id);
        return view('admin.room.edit', compact('room'));
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        $room->update($request->all());
        return redirect()->route('admin.room.index')->with('success', 'Data updated successfully');
    }

    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();
        return redirect()->route('admin.room.index')->with('success', 'Data deleted successfully');
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
            $type = $row[1] ?? 'Teori';
            $capacity = intval($row[2] ?? 40);
            $keterangan = $row[3] ?? '';

            Room::updateOrCreate(
                ['name' => $name],
                [
                    'type' => $type,
                    'capacity' => $capacity,
                    'keterangan' => $keterangan,
                    'is_active' => true
                ]
            );
            $importedCount++;
        }
        fclose($handle);

        return redirect()->route('admin.room.index')->with('success', "$importedCount data ruangan berhasil diimport.");
    }
}
