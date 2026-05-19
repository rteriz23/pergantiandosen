<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMahasiswaJadwalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mahasiswa_jadwal', function (Blueprint $table) {
            $table->id();
            // Kunci mahasiswa dan jadwal
            $table->unsignedBigInteger('mahasiswa_id');
            $table->unsignedBigInteger('schedule_id');
            // Tipe enrollment: 'reguler' atau 'pengulang'
            $table->string('tipe_enrollment')->default('reguler');
            // Periode akademik (copy dari schedule untuk filter cepat)
            $table->string('periode')->nullable();
            // Catatan khusus enrollment ini
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('cascade');
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            // Satu mahasiswa hanya bisa enroll satu kali ke satu jadwal
            $table->unique(['mahasiswa_id', 'schedule_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mahasiswa_jadwal');
    }
}
