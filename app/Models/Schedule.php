<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prodi_id',
        'periode',
        'mata_kuliah',
        'kelas',
        'pertemuan',
        'waktu_mulai',
        'waktu_selesai',
        'status',
        'dosen_pengganti_id',
        'room_id'
    ];

    public function dosen()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function dosenPengganti()
    {
        return $this->belongsTo(User::class, 'dosen_pengganti_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
