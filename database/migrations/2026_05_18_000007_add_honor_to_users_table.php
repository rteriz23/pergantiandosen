<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHonorToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('honor_per_jam', 10, 2)->nullable()->after('phone_number');
            $table->string('nidn')->nullable()->after('honor_per_jam');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['honor_per_jam', 'nidn']);
        });
    }
}
