<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'capacity', 'is_active', 'keterangan'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scheduleRequests()
    {
        return $this->hasMany(ScheduleRequest::class);
    }

    /**
     * Check if the room is occupied at a given time range.
     * Returns conflicting requests or empty collection.
     */
    public function conflictsAt($start, $end, $excludeRequestId = null)
    {
        $query = ScheduleRequest::where('room_id', $this->id)
            ->whereIn('status', ['Pending', 'Disetujui'])
            ->where('waktu_mulai_usulan', '<', $end)
            ->where('waktu_selesai_usulan', '>', $start);

        if ($excludeRequestId) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return $query->with('schedule.dosen')->get();
    }
}
