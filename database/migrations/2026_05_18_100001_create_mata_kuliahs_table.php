<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMataKuliahsTable extends Migration
{
    public function up()
    {
        Schema::create('mata_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->integer('sks')->default(3);
            $table->enum('jenis', ['teori', 'praktikum', 'campuran'])->default('teori');
            $table->unsignedBigInteger('prodi_id')->nullable();
            $table->boolean('butuh_lab')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->foreign('prodi_id')->references('id')->on('prodis')->onDelete('set null');
        });
    }

    public function down() { Schema::dropIfExists('mata_kuliahs'); }
}
