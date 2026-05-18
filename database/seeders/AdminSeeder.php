<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@lpkia.ac.id'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone_number' => '080000000000',
            ]
        );
        
        $this->command->info('Seeder Admin berhasil dijalankan!');
    }
}
