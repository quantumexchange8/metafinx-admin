<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingBonus extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'type',
        'amount',
        'release_date',
    ];

    protected $casts = [
        'release_date' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $settingBonus = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('setting_bonus')
            ->logOnly(['id', 'name', 'type', 'amount', 'release_date'])
            ->setDescriptionForEvent(function (string $eventName) use ($settingBonus) {
                $actorName = Auth::user() ? Auth::user()->name : 'System ';
                return "{$actorName} has {$eventName} setting bonus with ID: {$settingBonus->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

}
