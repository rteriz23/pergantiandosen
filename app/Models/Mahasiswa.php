<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nim', 'nama', 'email', 'prodi_id', 'kelas', 'status_mengulang'
    ];

    protected $casts = [
        'status_mengulang' => 'boolean',
    ];

    // ── Accessor: $mahasiswa->name => maps to nama ─────────────
    public function getNameAttribute()
    {
        return $this->nama;
    }

    // ── Relations ─────────────────────────────────────────────

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    /**
     * Explicit enrollment records (mahasiswa_jadwal pivot).
     */
    public function enrollments()
    {
        return $this->hasMany(MahasiswaJadwal::class);
    }

    /**
     * Enrolled schedules via mahasiswa_jadwal pivot.
     */
    public function jadwals()
    {
        return $this->belongsToMany(Schedule::class, 'mahasiswa_jadwal')
                    ->withPivot('tipe_enrollment', 'periode', 'catatan')
                    ->withTimestamps();
    }

    /**
     * Dynamic: schedules for this student's class (kelas-based, tanpa explicit enrollment).
     * Digunakan sebagai fallback saat enrollment belum diset secara manual.
     */
    public function jadwalsByKelas($periode = null)
    {
        $query = Schedule::where('kelas', $this->kelas)
                         ->with(['dosen', 'room', 'prodi']);
        if ($periode) {
            $query->where('periode', $periode);
        }
        return $query->orderBy('waktu_mulai')->get();
    }

    /**
     * Gabungan jadwal: dari explicit enrollment ATAU dari kelas.
     * Sorted by waktu_mulai.
     */
    public function allJadwals($periode = null)
    {
        $explicit = $this->jadwals()->with(['dosen', 'room', 'prodi']);
        if ($periode) {
            $explicit->where('schedules.periode', $periode);
        }
        $explicitIds = $explicit->pluck('id');

        // Tambah yang dari kelas tapi belum di-enroll secara eksplisit
        $query = Schedule::with(['dosen', 'room', 'prodi'])
                         ->where('kelas', $this->kelas);
        if ($periode) {
            $query->where('periode', $periode);
        }
        if ($explicitIds->count()) {
            $query->whereNotIn('id', $explicitIds);
        }
        $byKelas = $query->get();

        return $explicit->get()->concat($byKelas)->sortBy('waktu_mulai')->values();
    }

    /**
     * Count how many schedule replacement requests this student has submitted.
     */
    public function submissionCount($periode = null)
    {
        $query = ScheduleRequest::where('pengaju_nim_nidn', $this->nim)
            ->where('pengaju_type', 'mahasiswa');

        if ($periode) {
            $query->whereHas('schedule', fn($q) => $q->where('periode', $periode));
        }

        return $query->count();
    }

    /**
     * Total SKS pending/disetujui yang sudah diajukan mahasiswa ini.
     */
    public function totalSksAjuan()
    {
        $reqs = ScheduleRequest::where('pengaju_nim_nidn', $this->nim)
            ->where('pengaju_type', 'mahasiswa')
            ->whereIn('status', ['Pending', 'Disetujui'])
            ->with('schedule')
            ->get();

        $total = 0;
        foreach ($reqs as $req) {
            $sks = 3;
            if ($req->schedule && preg_match('/\((\d+)\s*SKS\)/i', $req->schedule->mata_kuliah, $m)) {
                $sks = (int) $m[1];
            }
            $total += $sks;
        }
        return $total;
    }
}
