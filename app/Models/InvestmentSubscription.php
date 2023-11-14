<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InvestmentSubscription extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'investment_plan_id',
        'wallet_id',
        'amount',
        'status',
        'remark',
        'next_roi_date',
        'expired_date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $investmentSubscription = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('investment_subscription')
            ->logOnly(['id', 'user_id', 'subscription_id', 'status', 'remark'])
            ->setDescriptionForEvent(function (string $eventName) use ($investmentSubscription) {
                return Auth::user()->name . " has {$eventName} subscription_id of {$investmentSubscription->subscription_id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function investment_plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InvestmentPlan::class, 'investment_plan_id', 'id');
    }
}
