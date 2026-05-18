<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaSetting extends Model
{
    use HasFactory;

    protected $table = 'sla_settings';

    protected $fillable = [
        'jam_sla', 'updated_by'
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the current SLA hours. Returns 48 as default if not configured.
     */
    public static function getCurrent(): int
    {
        return static::latest()->value('jam_sla') ?? 48;
    }
}
