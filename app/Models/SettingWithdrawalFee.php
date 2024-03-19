<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingWithdrawalFee extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'amount',
        'updated_by',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        $settingWithdrawalFee = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('setting_withdrawal_fee')
            ->logOnly(['id', 'amount', 'updated_by'])
            ->setDescriptionForEvent(function (string $eventName) use ($settingWithdrawalFee) {
                $actorName = Auth::user() ? Auth::user()->name : 'System ';
                return "{$actorName} has {$eventName} setting withdrawal fee with ID: {$settingWithdrawalFee->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

}
