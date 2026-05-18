<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $prodis = [
            'S1 - Teknik Informatika' => [
                'kaprodi' => 'Wahyu Nurjaya WK',
                'email' => 'kaprodi_if@lpkia.ac.id'
            ],
            'S1 - Sistem Informasi' => [
                'kaprodi' => 'Deden Sofyan Hamdani',
                'email' => 'kaprodi_si@lpkia.ac.id'
            ],
            'S1 - Akuntansi' => [
                'kaprodi' => 'Hamidah',
                'email' => 'kaprodi_ak_s1@lpkia.ac.id'
            ],
            'D3 - Akuntansi' => [
                'kaprodi' => 'Junaedi',
                'email' => 'kaprodi_ak_d3@lpkia.ac.id'
            ],
            'S1 - Administrasi Bisnis' => [
                'kaprodi' => 'Tengku Ine',
                'email' => 'kaprodi_adbis_s1@lpkia.ac.id'
            ],
            'D3 - Administrasi Bisnis' => [
                'kaprodi' => 'Rini Ratnaningsih',
                'email' => 'kaprodi_adbis_d3@lpkia.ac.id'
            ],
            'D1 - Modul' => [
                'kaprodi' => 'Rini Ratnaningsih (D1)',
                'email' => 'kaprodi_d1@lpkia.ac.id'
            ],
        ];

        foreach ($prodis as $prodiName => $data) {
            // Kita gunakan firstOrCreate agar jika prodi sudah ada (dari script python), kita gunakan id-nya
            $prodi = Prodi::firstOrCreate(['name' => $prodiName]);

            // Buat user kaprodi
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['kaprodi'],
                    'password' => Hash::make('password'),
                    'role' => 'kaprodi',
                    'prodi_id' => $prodi->id
                ]
            );
        }

        // Buat user BAA
        User::updateOrCreate(
            ['email' => 'baa@lpkia.ac.id'],
            [
                'name' => 'Admin BAA LPKIA',
                'password' => Hash::make('password'),
                'role' => 'baa',
                'phone_number' => '080000000000',
            ]
        );

        $this->command->info('Seeder Prodi, Kaprodi, dan BAA berhasil dijalankan!');

        // Call the schedule seeder which runs the python PDF parser
        $this->call([
            ScheduleSeeder::class,
        ]);
    }
}
