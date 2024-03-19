<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinStacking extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'coin_id',
        'transaction_id',
        'investment_plan_id',
        'subscription_number',
        'type',
        'stacking_unit',
        'stacking_price',
        'stacking_fee',
        'total_earning',
        'status',
        'remarks',
        'next_roi_date',
        'expired_date',
        'terminated_date',
        'max_capped_price',
        'reinvest_number',
    ];

    protected $casts = [
        'next_roi_date' => 'date',
        'expired_date' => 'date',
        'terminated_date' => 'date',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function investment_plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InvestmentPlan::class, 'investment_plan_id', 'id');
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    public function coin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Coin::class, 'coin_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        $coinStacking = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('coin_stacking')
            ->logOnly(['id', 'user_id', 'coin_id', 'transaction_id', 'investment_plan_id', 'subscription_number', 'type', 'stacking_unit', 'stacking_price', 'stacking_fee', 'total_earning', 'status', 'remarks', 'next_roi_date', 'expired_date', 'terminated_date', 'max_capped_price', 'reinvest_number'])
            ->setDescriptionForEvent(function (string $eventName) use ($coinStacking) {
                $actorName = Auth::user() ? Auth::user()->name : 'System ';
                return "{$actorName} has {$eventName} coin stacking with ID: {$coinStacking->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

}
