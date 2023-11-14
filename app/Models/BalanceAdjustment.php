<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BalanceAdjustment extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'to_user_id',
        'to_wallet_id',
        'type',
        'old_balance',
        'amount',
        'new_balance',
        'description',
        'handle_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $balanceAdjustment = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('balance_adjustment')
            ->logOnly(['id', 'user_id', 'wallet_id', 'to_user_id', 'to_wallet_id', 'type', 'old_balance', 'amount', 'new_balance', 'description', 'handle_by'])
            ->setDescriptionForEvent(function (string $eventName) use ($balanceAdjustment) {
                return Auth::user()->name . " has {$eventName} balance_adjustment_id of {$balanceAdjustment->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function to_user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id', 'id');
    }
}
