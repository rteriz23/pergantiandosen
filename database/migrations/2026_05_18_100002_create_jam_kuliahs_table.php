<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateJamKuliahsTable extends Migration
{
    public function up()
    {
        Schema::create('jam_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->string('label');          // e.g. "Sesi 1"
            $table->time('jam_mulai');        // e.g. 07:30
            $table->time('jam_selesai');      // e.g. 09:10
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default jam kuliah LPKIA
        $slots = [
            ['label' => 'Sesi 1', 'jam_mulai' => '07:30', 'jam_selesai' => '09:10', 'urutan' => 1],
            ['label' => 'Sesi 2', 'jam_mulai' => '09:20', 'jam_selesai' => '11:00', 'urutan' => 2],
            ['label' => 'Sesi 3', 'jam_mulai' => '11:10', 'jam_selesai' => '12:50', 'urutan' => 3],
            ['label' => 'Sesi 4', 'jam_mulai' => '13:00', 'jam_selesai' => '14:40', 'urutan' => 4],
            ['label' => 'Sesi 5', 'jam_mulai' => '14:50', 'jam_selesai' => '16:30', 'urutan' => 5],
            ['label' => 'Sesi 6', 'jam_mulai' => '16:40', 'jam_selesai' => '18:20', 'urutan' => 6],
            ['label' => 'Sesi 7', 'jam_mulai' => '18:30', 'jam_selesai' => '20:10', 'urutan' => 7],
        ];
        foreach ($slots as $s) {
            DB::table('jam_kuliahs')->insert(array_merge($s, ['created_at' => now(), 'updated_at' => now()]));
        }
    }

    public function down() { Schema::dropIfExists('jam_kuliahs'); }
}
