<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ExpandScheduleRequestsTable extends Migration
{
    public function up()
    {
        // For SQLite: rebuild the table to make pengaju_id nullable
        // SQLite does not support ALTER COLUMN, so we recreate the table
        if (DB::getDriverName() === 'sqlite') {
            // Step 1: Create temp table with new schema
            DB::statement('PRAGMA foreign_keys=OFF');
            DB::statement('
                CREATE TABLE schedule_requests_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    schedule_id INTEGER NOT NULL,
                    pengaju_id INTEGER NULL,
                    pengaju_nama VARCHAR(255) NULL,
                    pengaju_nim_nidn VARCHAR(255) NULL,
                    pengaju_type VARCHAR(255) NOT NULL DEFAULT \'dosen\',
                    pengaju_email VARCHAR(255) NULL,
                    waktu_mulai_usulan DATETIME NOT NULL,
                    waktu_selesai_usulan DATETIME NOT NULL,
                    ruangan_usulan VARCHAR(255) NULL,
                    room_id INTEGER NULL,
                    alasan TEXT NULL,
                    is_online TINYINT(1) NOT NULL DEFAULT 0,
                    status VARCHAR(255) NOT NULL DEFAULT \'Pending\',
                    sla_deadline DATETIME NULL,
                    approved_at DATETIME NULL,
                    rejected_at DATETIME NULL,
                    catatan_kaprodi TEXT NULL,
                    catatan_baa TEXT NULL,
                    jam_presensi_dosen DECIMAL(5,2) NULL,
                    created_at DATETIME NULL,
                    updated_at DATETIME NULL
                )
            ');
            // Step 2: Copy data
            DB::statement('
                INSERT INTO schedule_requests_new
                    (id, schedule_id, pengaju_id, waktu_mulai_usulan, waktu_selesai_usulan,
                     ruangan_usulan, alasan, status, catatan_kaprodi, created_at, updated_at)
                SELECT
                    id, schedule_id, pengaju_id, waktu_mulai_usulan, waktu_selesai_usulan,
                    ruangan_usulan, alasan, status, catatan_kaprodi, created_at, updated_at
                FROM schedule_requests
            ');
            // Step 3: Drop old, rename new
            DB::statement('DROP TABLE schedule_requests');
            DB::statement('ALTER TABLE schedule_requests_new RENAME TO schedule_requests');
            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            // MySQL: use MODIFY COLUMN
            DB::statement('ALTER TABLE schedule_requests MODIFY COLUMN pengaju_id BIGINT UNSIGNED NULL');
            Schema::table('schedule_requests', function (Blueprint $table) {
                $table->string('pengaju_nama')->nullable()->after('pengaju_id');
                $table->string('pengaju_nim_nidn')->nullable()->after('pengaju_nama');
                $table->string('pengaju_type')->default('dosen')->after('pengaju_nim_nidn');
                $table->string('pengaju_email')->nullable()->after('pengaju_type');
                $table->boolean('is_online')->default(false)->after('alasan');
                $table->dateTime('sla_deadline')->nullable()->after('status');
                $table->dateTime('approved_at')->nullable()->after('sla_deadline');
                $table->dateTime('rejected_at')->nullable()->after('approved_at');
                $table->text('catatan_baa')->nullable()->after('catatan_kaprodi');
                $table->decimal('jam_presensi_dosen', 5, 2)->nullable()->after('catatan_baa');
                $table->unsignedBigInteger('room_id')->nullable()->after('ruangan_usulan');
            });
        }
    }

    public function down()
    {
        // For simplicity in rollback, just drop added columns on non-SQLite
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('schedule_requests', function (Blueprint $table) {
                $table->dropColumn([
                    'pengaju_nama', 'pengaju_nim_nidn', 'pengaju_type', 'pengaju_email',
                    'is_online', 'sla_deadline', 'approved_at', 'rejected_at',
                    'catatan_baa', 'jam_presensi_dosen', 'room_id'
                ]);
            });
        }
    }
}
