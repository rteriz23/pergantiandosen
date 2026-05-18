<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ScheduleRequest;

class ScheduleRequestedNotification extends Notification
{
    use Queueable;

    protected $scheduleRequest;

    public function __construct(ScheduleRequest $scheduleRequest)
    {
        $this->scheduleRequest = $scheduleRequest;
    }

    public function via($notifiable)
    {
        return ['database']; // For this phase, we only use in-app DB notification
    }

    public function toDatabase($notifiable)
    {
        $dosenName = $this->scheduleRequest->schedule->dosen->name ?? 'Dosen';
        $mataKuliah = $this->scheduleRequest->schedule->mata_kuliah;
        
        $url = $notifiable->role == 'baa' ? route('baa.requests') : route('kaprodi.requests');
        
        return [
            'request_id' => $this->scheduleRequest->id,
            'dosen_name' => $dosenName,
            'message' => "{$dosenName} mengajukan pergantian jadwal untuk mata kuliah {$mataKuliah}.",
            'url' => $url
        ];
    }
}
