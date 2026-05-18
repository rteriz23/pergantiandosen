<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KalenderAkademik extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'judul',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'warna'
    ];
}
