<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prodi;

class UpdateProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $prodis = [
            'INFORMATIKA',
            'SISTEM INFORMASI',
            'KOMPUTERISASI AKUNTANSI',
            'ADMINISTRASI BISNIS',
            'MODUL DIGITALISASI BISNIS'
        ];

        foreach ($prodis as $prodiName) {
            Prodi::firstOrCreate(['name' => $prodiName]);
        }
        
        $this->command->info('Prodi baru berhasil ditambahkan!');
    }
}
