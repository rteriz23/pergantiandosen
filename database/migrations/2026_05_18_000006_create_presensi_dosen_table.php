<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresensiDosenTable extends Migration
{
    public function up()
    {
        Schema::create('presensi_dosen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_request_id')->nullable();
            $table->unsignedBigInteger('schedule_id')->nullable(); // direct schedule link
            $table->unsignedBigInteger('dosen_id');
            $table->date('tanggal_hadir');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->decimal('durasi_jam', 5, 2); // computed hours
            $table->decimal('honor_per_jam', 10, 2)->default(0);
            $table->decimal('honor_total', 10, 2)->default(0); // durasi * honor_per_jam
            $table->string('status_kbm')->default('hadir'); // hadir, online, izin, sakit
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('dicatat_oleh')->nullable(); // BAA user id
            $table->timestamps();

            $table->foreign('schedule_request_id')->references('id')->on('schedule_requests')->onDelete('set null');
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('set null');
            $table->foreign('dosen_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('dicatat_oleh')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('presensi_dosen');
    }
}
