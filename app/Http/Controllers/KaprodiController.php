<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduleRequest;
use App\Models\Schedule;
use App\Models\Room;
use App\Models\User;
use App\Models\SlaSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class KaprodiController extends Controller
{
    protected function authorizeKaprodi()
    {
        if (Auth::user()->role !== 'kaprodi') abort(403);
        return Auth::user();
    }

    public function requests(Request $request)
    {
        $user = $this->authorizeKaprodi();

        $query = ScheduleRequest::with(['schedule.dosen', 'schedule.prodi', 'pengaju', 'room'])
            ->whereHas('schedule', function($q) use ($user) { $q->where('prodi_id', $user->prodi_id); })
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->get();

        if ($request->has('read_notification')) {
            $notif = $user->notifications()->find($request->read_notification);
            if ($notif) { $notif->markAsRead(); }
        }

        $rooms = Room::where('is_active', true)->orderBy('name')->get();
        $slaHours = SlaSetting::getCurrent();

        return view('kaprodi.requests', compact('requests', 'rooms', 'slaHours'));
    }

    public function approve(Request $request, $id)
    {
        $this->authorizeKaprodi();
        $req = ScheduleRequest::with('schedule')->findOrFail($id);
        $req->status          = 'Disetujui';
        $req->catatan_kaprodi = $request->catatan;
        $req->approved_at     = now();
        $req->is_online       = $request->boolean('is_online');
        $req->save();

        $schedule = $req->schedule;
        if ($schedule) {
            $schedule->status = 'Diganti';
            $schedule->save();
            Schedule::create([
                'user_id'      => $schedule->user_id,
                'prodi_id'     => $schedule->prodi_id,
                'periode'      => $schedule->periode,
                'mata_kuliah'  => $schedule->mata_kuliah,
                'kelas'        => $schedule->kelas,
                'pertemuan'    => $schedule->pertemuan,
                'waktu_mulai'  => $req->waktu_mulai_usulan,
                'waktu_selesai' => $req->waktu_selesai_usulan,
                'status'       => 'Terjadwal',
            ]);
        }

        if ($req->pengaju_id) {
            $req->pengaju->notify(new \App\Notifications\ScheduleRequestedNotification($req));
        }

        return back()->with('success', 'Permohonan disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $this->authorizeKaprodi();
        $req = ScheduleRequest::findOrFail($id);
        $req->status          = 'Ditolak';
        $req->catatan_kaprodi = $request->catatan;
        $req->rejected_at     = now();
        $req->save();

        if ($req->pengaju_id) {
            $req->pengaju->notify(new \App\Notifications\ScheduleRequestedNotification($req));
        }

        return back()->with('success', 'Permohonan ditolak.');
    }

    public function suggestSlots(Request $request, $id)
    {
        $this->authorizeKaprodi();
        $req     = ScheduleRequest::with('schedule.dosen')->findOrFail($id);
        $dosen   = $req->schedule->dosen;
        $dosenId = $dosen->id;

        $suggestions = [];
        $checkStart  = now()->startOfWeek();

        for ($week = 0; $week < 4 && count($suggestions) < 5; $week++) {
            for ($day = 1; $day <= 6 && count($suggestions) < 5; $day++) {
                $date = $checkStart->copy()->addWeeks($week)->addDays($day - 1);
                if ($date->isPast()) continue;

                $existing = Schedule::where('user_id', $dosenId)
                    ->whereDate('waktu_mulai', $date)->exists();

                $existingReq = ScheduleRequest::whereHas('schedule', function($q) use ($dosenId) { $q->where('user_id', $dosenId); })
                    ->whereDate('waktu_mulai_usulan', $date)
                    ->whereIn('status', ['Pending', 'Disetujui'])->exists();

                if (!$existing && !$existingReq) {
                    $suggestions[] = [
                        'date'  => $date->format('Y-m-d'),
                        'label' => $date->translatedFormat('l, d M Y'),
                    ];
                }
            }
        }

        return response()->json(['slots' => $suggestions, 'dosen' => $dosen->name]);
    }

    public function calendarView()
    {
        $user   = $this->authorizeKaprodi();
        $rooms  = Room::where('is_active', true)->orderBy('name')->get();
        $dosens = User::where('role', 'dosen')
            ->whereHas('schedules', function($q) use ($user) { $q->where('prodi_id', $user->prodi_id); })
            ->orderBy('name')->get();
        $slaHours = SlaSetting::getCurrent();

        return view('kaprodi.calendar', compact('rooms', 'dosens', 'slaHours'));
    }
}
