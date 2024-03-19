<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'subject',
        'details',
        'receiver_type',
        'receiver',
    ];

    protected $casts = [
        'receiver' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $announcement = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('announcement')
            ->logOnly(['id', 'subject', 'details', 'receiver_type', 'receiver'])
            ->setDescriptionForEvent(function (string $eventName) use ($announcement) {
                $actorName = Auth::user() ? Auth::user()->name : 'System ';
                return "{$actorName} has {$eventName} announcement with ID: {$announcement->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

}
