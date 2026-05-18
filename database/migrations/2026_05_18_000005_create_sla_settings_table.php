<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlaSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('sla_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('jam_sla')->default(48); // hours to approve
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        // Insert default SLA
        DB::table('sla_settings')->insert([
            'jam_sla' => 48,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('sla_settings');
    }
}
