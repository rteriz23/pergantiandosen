<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRuanganUsulanToScheduleRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->string('ruangan_usulan')->nullable()->after('waktu_selesai_usulan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->dropColumn('ruangan_usulan');
        });
    }
}
