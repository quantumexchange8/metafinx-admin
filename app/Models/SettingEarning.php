<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SettingEarning extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'setting_rank_id',
        'name',
        'type',
        'value',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $setting_earning = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('setting_earning')
            ->logOnly(['setting_rank_id', 'name', 'type', 'value'])
            ->setDescriptionForEvent(function (string $eventName) use ($setting_earning) {
                $actorName = Auth::user() ? Auth::user()->name : 'Cronjob ';
                return "{$actorName} has {$eventName} Setting Earning ID: {$setting_earning->id}, {$setting_earning->name}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
