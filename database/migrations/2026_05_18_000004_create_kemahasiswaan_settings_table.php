<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKemahasiswaanSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('kemahasiswaan_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prodi_id')->nullable(); // null = global default
            $table->integer('max_pergantian')->default(3);      // max replacements per student
            $table->integer('max_sks')->nullable();             // optional SKS cap advisory
            $table->timestamps();

            $table->foreign('prodi_id')->references('id')->on('prodis')->onDelete('cascade');
        });

        // Insert global default
        DB::table('kemahasiswaan_settings')->insert([
            'prodi_id' => null,
            'max_pergantian' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('kemahasiswaan_settings');
    }
}
