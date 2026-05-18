<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('pengaju_id'); // User ID Dosen
            $table->dateTime('waktu_mulai_usulan');
            $table->dateTime('waktu_selesai_usulan');
            $table->text('alasan')->nullable();
            $table->string('status')->default('Pending'); // Pending, Disetujui, Ditolak
            $table->text('catatan_kaprodi')->nullable();
            $table->timestamps();
            
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->foreign('pengaju_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_requests');
    }
}
