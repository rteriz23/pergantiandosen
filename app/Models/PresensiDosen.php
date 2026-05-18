<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiDosen extends Model
{
    use HasFactory;

    protected $table = 'presensi_dosen';

    protected $fillable = [
        'schedule_request_id',
        'schedule_id',
        'dosen_id',
        'tanggal_hadir',
        'jam_mulai',
        'jam_selesai',
        'durasi_jam',
        'honor_per_jam',
        'honor_total',
        'status_kbm',
        'catatan',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal_hadir' => 'date',
        'durasi_jam' => 'decimal:2',
        'honor_per_jam' => 'decimal:2',
        'honor_total' => 'decimal:2',
    ];

    public function scheduleRequest()
    {
        return $this->belongsTo(ScheduleRequest::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function dicatatOleh()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    /**
     * Auto-compute duration and honor when saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->jam_mulai && $model->jam_selesai) {
                $start = \Carbon\Carbon::parse($model->jam_mulai);
                $end = \Carbon\Carbon::parse($model->jam_selesai);
                $model->durasi_jam = round($end->diffInMinutes($start) / 60, 2);
                $model->honor_total = round($model->durasi_jam * ($model->honor_per_jam ?? 0), 2);
            }
        });
    }
}
