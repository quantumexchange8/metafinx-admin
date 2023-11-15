<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $announcement;

    public function __construct($announcement)
    {
        $this->announcement = $announcement;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => $this->announcement->subject,
            'content' => $this->announcement->details,
            'post_date' => $this->announcement->created_at,
            'image' => $this->announcement->getFirstMediaUrl('announcement'),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->announcement->id,
            'title' => $this->announcement->subject,
            'content' => $this->announcement->details,
            'post_date' => $this->announcement->created_at,
            'image' => $this->announcement->getFirstMediaUrl('announcement'),
        ];
    }
}
