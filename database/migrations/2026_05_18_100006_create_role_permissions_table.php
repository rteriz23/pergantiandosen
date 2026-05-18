<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolePermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('permission');
            $table->boolean('allowed')->default(true);
            $table->timestamps();
            $table->unique(['role', 'permission']);
        });

        // Seed default permissions
        $permissions = [
            // Kaprodi
            ['role' => 'kaprodi', 'permission' => 'approve_request'],
            ['role' => 'kaprodi', 'permission' => 'view_calendar'],
            ['role' => 'kaprodi', 'permission' => 'view_all_schedules'],
            // BAA
            ['role' => 'baa', 'permission' => 'approve_request'],
            ['role' => 'baa', 'permission' => 'manage_rooms'],
            ['role' => 'baa', 'permission' => 'manage_honor'],
            ['role' => 'baa', 'permission' => 'export_reports'],
            ['role' => 'baa', 'permission' => 'manage_sla'],
            ['role' => 'baa', 'permission' => 'manage_master_data'],
            // Kemahasiswaan
            ['role' => 'kemahasiswaan', 'permission' => 'manage_mahasiswa_quota'],
            ['role' => 'kemahasiswaan', 'permission' => 'view_mahasiswa'],
            // Admin
            ['role' => 'admin', 'permission' => 'manage_users'],
            ['role' => 'admin', 'permission' => 'manage_roles'],
            ['role' => 'admin', 'permission' => 'manage_master_data'],
            ['role' => 'admin', 'permission' => 'view_all'],
            // Laboran
            ['role' => 'laboran', 'permission' => 'approve_lab_request'],
            ['role' => 'laboran', 'permission' => 'manage_lab'],
            // Dosen
            ['role' => 'dosen', 'permission' => 'submit_request'],
            ['role' => 'dosen', 'permission' => 'view_own_schedule'],
            // Mahasiswa / Ketua Kelas
            ['role' => 'mahasiswa', 'permission' => 'submit_request'],
            ['role' => 'mahasiswa', 'permission' => 'view_own_requests'],
            ['role' => 'ketua_kelas', 'permission' => 'submit_request'],
            ['role' => 'ketua_kelas', 'permission' => 'view_kelas_requests'],
        ];

        foreach ($permissions as $p) {
            \DB::table('role_permissions')->insert(array_merge($p, ['allowed' => true, 'created_at' => now(), 'updated_at' => now()]));
        }
    }

    public function down() { Schema::dropIfExists('role_permissions'); }
}
