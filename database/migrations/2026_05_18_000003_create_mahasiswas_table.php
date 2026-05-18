<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMahasiswasTable extends Migration
{
    public function up()
    {
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->unsignedBigInteger('prodi_id')->nullable();
            $table->timestamps();

            $table->foreign('prodi_id')->references('id')->on('prodis')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mahasiswas');
    }
}
