<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KemahasiswaanSetting;
use App\Models\Mahasiswa;
use App\Models\ScheduleRequest;
use App\Models\Prodi;
use Illuminate\Support\Facades\Auth;

class KemahasiswaanController extends Controller
{
    protected function checkRole()
    {
        if (!in_array(Auth::user()->role, ['kemahasiswaan', 'baa'])) abort(403);
    }

    public function settings()
    {
        $this->checkRole();
        $prodis   = Prodi::orderBy('name')->get();
        $settings = KemahasiswaanSetting::with('prodi')->get()->keyBy('prodi_id');
        $global   = KemahasiswaanSetting::whereNull('prodi_id')->first();

        return view('kemahasiswaan.settings', compact('prodis', 'settings', 'global'));
    }

    public function updateSettings(Request $request)
    {
        $this->checkRole();
        $request->validate([
            'prodi_id'       => 'nullable|exists:prodis,id',
            'max_pergantian' => 'required|integer|min:1|max:20',
            'max_sks'        => 'nullable|integer|min:1',
        ]);

        KemahasiswaanSetting::updateOrCreate(
            ['prodi_id' => $request->prodi_id ?: null],
            ['max_pergantian' => $request->max_pergantian, 'max_sks' => $request->max_sks]
        );

        return back()->with('success', 'Pengaturan batas pergantian berhasil disimpan.');
    }

    public function mahasiswas(Request $request)
    {
        $this->checkRole();
        $mahasiswas = Mahasiswa::with('prodi')->orderBy('nama')->get();

        $mahasiswas->each(function ($m) {
            $m->submission_count = ScheduleRequest::where('pengaju_nim_nidn', $m->nim)
                ->where('pengaju_type', 'mahasiswa')->count();
            $setting = KemahasiswaanSetting::getFor($m->prodi_id);
            $m->max_pergantian = $setting->max_pergantian;
        });

        return view('kemahasiswaan.mahasiswas', compact('mahasiswas'));
    }
}
