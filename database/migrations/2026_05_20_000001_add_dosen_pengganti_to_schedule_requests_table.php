<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDosenPenggantiToScheduleRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('dosen_pengganti_id')->nullable()->after('schedule_id');
            
            $table->foreign('dosen_pengganti_id')->references('id')->on('users')->onDelete('set null');
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
            $table->dropForeign(['dosen_pengganti_id']);
            $table->dropColumn('dosen_pengganti_id');
        });
    }
}
