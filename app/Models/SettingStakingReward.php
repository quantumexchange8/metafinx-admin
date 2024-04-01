<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingStakingReward extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'month',
        'release_date',
        'percent',
        'is_done',
        'updated_by',
        'start_of_month',
        'end_of_month',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        $settingStakingReward = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('setting_staking_reward')
            ->logOnly(['id', 'month', 'release_date', 'percent', 'is_done', 'updated_by'])
            ->setDescriptionForEvent(function (string $eventName) use ($settingStakingReward) {
                $actorName = Auth::user() ? Auth::user()->name : 'System ';
                return "{$actorName} has {$eventName} setting staking reward with ID: {$settingStakingReward->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

}
