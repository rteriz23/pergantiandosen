<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MahasiswaJadwal extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa_jadwal';

    protected $fillable = [
        'mahasiswa_id',
        'schedule_id',
        'tipe_enrollment', // 'reguler' atau 'pengulang'
        'periode',
        'catatan',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class)->with(['dosen', 'room', 'prodi']);
    }
}
