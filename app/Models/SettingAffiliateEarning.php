<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SettingAffiliateEarning extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'setting_rank_id',
        'name',
        'value',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $setting_affiliate_earning = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('setting_affiliate_earning')
            ->logOnly(['setting_rank_id', 'name', 'value'])
            ->setDescriptionForEvent(function (string $eventName) use ($setting_affiliate_earning) {
                $actorName = Auth::user() ? Auth::user()->name : 'Cronjob ';
                return "{$actorName} has {$eventName} Setting Affiliate Earning ID: {$setting_affiliate_earning->id}, {$setting_affiliate_earning->name}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
