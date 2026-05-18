<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nim', 'nama', 'email', 'prodi_id'
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    /**
     * Count how many schedule replacement requests this student has submitted
     * in the current or given periode.
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
}
