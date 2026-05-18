<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKalenderAkademiksTable extends Migration
{
    public function up()
    {
        Schema::create('kalender_akademiks', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('warna')->default('#4F46E5');
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists('kalender_akademiks'); }
}
