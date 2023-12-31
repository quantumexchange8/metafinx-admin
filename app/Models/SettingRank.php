<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SettingRank extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'self_deposit',
        'valid_direct_referral',
        'valid_affiliate_deposit',
        'capping_per_line',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $rank = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('setting_rank')
            ->logOnly(['name', 'self_deposit', 'valid_direct_referral', 'valid_affiliate_deposit', 'capping_per_line'])
            ->setDescriptionForEvent(function (string $eventName) use ($rank) {
                $actorName = Auth::user() ? Auth::user()->name : 'Cronjob ';
                return "{$actorName} has {$eventName} Setting Rank ID: {$rank->id}, {$rank->name}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
