<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaboratoriumsTable extends Migration
{
    public function up()
    {
        Schema::create('laboratoriums', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->nullable()->unique();
            $table->integer('kapasitas')->default(30);
            $table->unsignedBigInteger('laboran_id')->nullable();   // user with role=laboran
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('bisa_online')->default(false);         // can KBM go online?
            $table->timestamps();
            $table->foreign('laboran_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down() { Schema::dropIfExists('laboratoriums'); }
}
