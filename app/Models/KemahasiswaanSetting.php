<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KemahasiswaanSetting extends Model
{
    use HasFactory;

    protected $table = 'kemahasiswaan_settings';

    protected $fillable = [
        'prodi_id', 'max_pergantian', 'max_sks'
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    /**
     * Get the applicable setting for a given prodi_id.
     * Falls back to global (prodi_id = null) if no prodi-specific setting exists.
     */
    public static function getFor($prodiId)
    {
        return static::where('prodi_id', $prodiId)->first()
            ?? static::whereNull('prodi_id')->first()
            ?? (object)['max_pergantian' => 3, 'max_sks' => null];
    }
}
