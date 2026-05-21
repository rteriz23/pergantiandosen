<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ScheduleRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'dosen_pengganti_id',
        'pengaju_id',
        'pengaju_nama',
        'pengaju_nim_nidn',
        'pengaju_type',
        'pengaju_email',
        'waktu_mulai_usulan',
        'waktu_selesai_usulan',
        'ruangan_usulan',
        'room_id',
        'alasan',
        'is_online',
        'status',
        'sla_deadline',
        'approved_at',
        'rejected_at',
        'catatan_kaprodi',
        'catatan_baa',
        'jam_presensi_dosen',
    ];

    protected $casts = [
        'is_online'       => 'boolean',
        'sla_deadline'    => 'datetime',
        'approved_at'     => 'datetime',
        'rejected_at'     => 'datetime',
        'waktu_mulai_usulan'  => 'datetime',
        'waktu_selesai_usulan' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'pengaju_id');
    }

    public function dosenPengganti()
    {
        return $this->belongsTo(User::class, 'dosen_pengganti_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function presensi()
    {
        return $this->hasMany(PresensiDosen::class, 'schedule_request_id');
    }

    // ── Helpers ───────────────────────────────────────────────

    /**
     * Display name of whoever submitted this request (user or anonymous).
     */
    public function getPengajuDisplayNameAttribute(): string
    {
        if ($this->pengaju_id && $this->pengaju) {
            return $this->pengaju->name;
        }
        return $this->pengaju_nama ?? '(Anonim)';
    }

    /**
     * Compute SLA status: 'ok' | 'warning' | 'breach'
     */
    public function getSlaStatusAttribute(): string
    {
        if ($this->status !== 'Pending' || !$this->sla_deadline) return 'ok';
        $hoursLeft = now()->diffInHours($this->sla_deadline, false);
        if ($hoursLeft < 0)  return 'breach';
        if ($hoursLeft < 12) return 'warning';
        return 'ok';
    }

    /**
     * Hours remaining until SLA deadline (negative if breached).
     */
    public function getSlaHoursLeftAttribute(): int
    {
        if (!$this->sla_deadline) return 999;
        return (int) now()->diffInHours($this->sla_deadline, false);
    }
}
