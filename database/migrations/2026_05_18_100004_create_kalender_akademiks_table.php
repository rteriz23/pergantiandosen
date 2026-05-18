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
            $table->unsignedBigInteger('tahun_akademik_id')->nullable();
            $table->date('tanggal');
            $table->string('keterangan');
            $table->boolean('is_libur')->default(true);   // libur = tidak bisa dijadwalkan
            $table->enum('jenis', ['libur_nasional', 'libur_kampus', 'ujian', 'kegiatan', 'lainnya'])->default('lainnya');
            $table->timestamps();
            $table->foreign('tahun_akademik_id')->references('id')->on('tahun_akademiks')->onDelete('set null');
        });
    }

    public function down() { Schema::dropIfExists('kalender_akademiks'); }
}
