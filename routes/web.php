<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\KaprodiController;
use App\Http\Controllers\BaaController;
use App\Http\Controllers\KemahasiswaanController;
use App\Http\Controllers\NotificationController;

// ── Public Routes (no auth required) ────────────────────────────────────────
Route::get('/', [ScheduleController::class, 'publicIndex'])->name('schedules.public');

// Public API
Route::get('/api/schedules', [ScheduleController::class, 'apiSchedules'])->name('api.schedules');
Route::get('/api/availability', [ScheduleController::class, 'checkAvailability'])->name('api.availability');
Route::get('/api/rooms', [ScheduleController::class, 'apiRooms'])->name('api.rooms');

// Public Request Pages (no login needed)
Route::get('/schedules/request/new', [ScheduleController::class, 'requestGeneral'])->name('schedules.request_new');
Route::get('/schedules/request/{id}', [ScheduleController::class, 'requestChange'])->name('schedules.request');
Route::post('/schedules/request/{id}', [ScheduleController::class, 'storeRequest'])->name('schedules.storeRequest');

// ── Authenticated Routes ──────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notification polling (works for all logged-in roles)
    Route::get('/api/notifications/poll', [NotificationController::class, 'poll'])->name('api.notifications.poll');
    Route::post('/api/notifications/read', [NotificationController::class, 'markRead'])->name('api.notifications.read');

    // ── Kaprodi Routes ──────────────────────────────────────────────────────
    Route::get('/kaprodi/requests', [KaprodiController::class, 'requests'])->name('kaprodi.requests');
    Route::post('/kaprodi/requests/{id}/approve', [KaprodiController::class, 'approve'])->name('kaprodi.approve');
    Route::post('/kaprodi/requests/{id}/reject', [KaprodiController::class, 'reject'])->name('kaprodi.reject');
    Route::get('/kaprodi/requests/{id}/suggest-slots', [KaprodiController::class, 'suggestSlots'])->name('kaprodi.suggestSlots');
    Route::get('/kaprodi/calendar', [KaprodiController::class, 'calendarView'])->name('kaprodi.calendar');

    // ── BAA Routes ──────────────────────────────────────────────────────────
    Route::get('/baa/requests', [BaaController::class, 'requests'])->name('baa.requests');
    Route::post('/baa/presensi', [BaaController::class, 'recordPresensi'])->name('baa.presensi.store');
    Route::get('/baa/honor/export', [BaaController::class, 'exportHonor'])->name('baa.honor.export');
    Route::post('/baa/honor/import', [BaaController::class, 'importHonor'])->name('baa.honor.import');
    Route::get('/baa/settings', [BaaController::class, 'settings'])->name('baa.settings');
    Route::post('/baa/settings', [BaaController::class, 'updateSettings'])->name('baa.settings.update');
    Route::post('/baa/kbm/toggle/{id}', [BaaController::class, 'toggleOnlineKBM'])->name('baa.kbm.toggle');
    Route::get('/baa/rooms', [BaaController::class, 'rooms'])->name('baa.rooms.index');
    Route::post('/baa/rooms', [BaaController::class, 'storeRoom'])->name('baa.rooms.store');
    Route::put('/baa/rooms/{room}', [BaaController::class, 'updateRoom'])->name('baa.rooms.update');
    Route::delete('/baa/rooms/{room}', [BaaController::class, 'destroyRoom'])->name('baa.rooms.destroy');
    Route::post('/baa/dosen/honor', [BaaController::class, 'updateDosenHonor'])->name('baa.dosen.honor');

    // Periode management (BAA)
    Route::get('/baa/periodes', [\App\Http\Controllers\Baa\PeriodeController::class, 'index'])->name('baa.periodes.index');
    Route::post('/baa/periodes', [\App\Http\Controllers\Baa\PeriodeController::class, 'store'])->name('baa.periodes.store');
    Route::put('/baa/periodes/{periode}', [\App\Http\Controllers\Baa\PeriodeController::class, 'update'])->name('baa.periodes.update');
    Route::delete('/baa/periodes/{periode}', [\App\Http\Controllers\Baa\PeriodeController::class, 'destroy'])->name('baa.periodes.destroy');

    // ── Kemahasiswaan Routes ────────────────────────────────────────────────
    Route::get('/kemahasiswaan/settings', [KemahasiswaanController::class, 'settings'])->name('kemahasiswaan.settings');
    Route::post('/kemahasiswaan/settings', [KemahasiswaanController::class, 'updateSettings'])->name('kemahasiswaan.settings.update');
    Route::get('/kemahasiswaan/mahasiswas', [KemahasiswaanController::class, 'mahasiswas'])->name('kemahasiswaan.mahasiswas');
});

require __DIR__ . '/auth.php';
