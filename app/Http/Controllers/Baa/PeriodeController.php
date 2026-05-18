<?php

namespace App\Http\Controllers\Baa;

use App\Http\Controllers\Controller;
use App\Models\Periode;
use Illuminate\Http\Request;

class PeriodeController extends Controller
{
    public function index()
    {
        $periodes = Periode::orderBy('name', 'desc')->get();
        return view('baa.periodes.index', compact('periodes'));
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
}
